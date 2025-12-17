// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Openpay UI inside the gateways modal.
 *
 * @module     paygw_openpay/gateways_modal
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import * as Repository from './repository';
import Templates from 'core/templates';
import Modal from 'core/modal';
import ModalEvents from 'core/modal_events';
import {getString, getStrings} from 'core/str';

const debug = (...args) => {
    window.console?.info?.('[paygw_openpay]', ...args);
};

const getErrorMessage = (err) => {
    if (!err) {
        return 'Unknown error';
    }

    if (typeof err === 'string') {
        return err;
    }

    const candidates = [
        err.message,
        err.error,
        err.description,
        err.error_description,
        err.debuginfo,
        err.exception,
        err?.data?.description,
        err?.data?.message,
    ].filter(Boolean);

    if (candidates.length) {
        return String(candidates[0]);
    }

    try {
        const str = JSON.stringify(err);
        if (str && str !== '{}' && str !== '""') {
            return str;
        }
    } catch (e) {
        // Ignore.
    }

    return 'Unknown error';
};

const loadScriptOnce = (src, globalName) => {
    if (globalName && window[globalName]) {
        return Promise.resolve();
    }

    const existing = document.querySelector(`script[src="${src}"]`);
    if (existing) {
        return Promise.resolve();
    }

    return new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = src;
        script.async = true;
        script.onload = () => resolve();
        script.onerror = () => reject(new Error(`Failed to load ${src}`));
        document.head.appendChild(script);
    });
};

const showModalWithForm = async() => {
    const body = await Templates.render('paygw_openpay/openpay_card_form', {});
    return Modal.create({
        body,
        show: true,
        removeOnClose: true,
    });
};

const setStatus = (modal, message, isError) => {
    const root = modal.getRoot()[0];
    const statusEl = root.querySelector('[data-region="status"]');
    if (!statusEl) {
        return;
    }
    statusEl.style.display = '';
    statusEl.className = isError ? 'alert alert-danger' : 'alert alert-info';
    statusEl.textContent = message;
};

export const process = async(component, paymentArea, itemId) => {
    try {
        const [modal, config, errorStrings] = await Promise.all([
            showModalWithForm(),
            Repository.getConfigForJs(component, paymentArea, itemId),
            getStrings([
                {key: 'error_holdername', component: 'paygw_openpay'},
                {key: 'error_cardnumber', component: 'paygw_openpay'},
                {key: 'error_expiration_month', component: 'paygw_openpay'},
                {key: 'error_expiration_year', component: 'paygw_openpay'},
                {key: 'error_cvv', component: 'paygw_openpay'},
                {key: 'error_forminvalid', component: 'paygw_openpay'},
            ]),
        ]);
        debug('Config fetched', {component, paymentArea, itemId, config});

        const validationMessages = {
            holder: errorStrings[0],
            card: errorStrings[1],
            expmonth: errorStrings[2],
            expyear: errorStrings[3],
            cvv: errorStrings[4],
            form: errorStrings[5],
        };

        // Prevent accidental close while processing.
        modal.getRoot().on(ModalEvents.outsideClick, (e) => e.preventDefault());

        // Load Openpay.js and openpay-data.js from S3 CDN (same script for MX/CO).
        await loadScriptOnce('https://openpay.s3.amazonaws.com/openpay.v1.min.js', 'OpenPay');
        await loadScriptOnce('https://openpay.s3.amazonaws.com/openpay-data.v1.min.js', 'OpenPay.deviceData');
        debug('Openpay.js loaded');

        if (!window.OpenPay) {
            throw new Error('OpenPay global not found after loading Openpay.js');
        }

        // Override internal hostnames to point to Colombia endpoints.
        window.OpenPay.hostname = 'https://api.openpay.co/v1/';
        window.OpenPay.sandboxHostname = 'https://sandbox-api.openpay.co/v1/';

        window.OpenPay.setId(config.merchantid);
        window.OpenPay.setApiKey(config.publickey);
        window.OpenPay.setSandboxMode(!!config.sandbox);

        const root = modal.getRoot()[0];
        const form = root.querySelector('#paygw_openpay_card_form');
        const cancelBtn = root.querySelector('[data-action="cancel"]');
        const payBtn = root.querySelector('[data-action="pay"]');

        // Generate device session ID for antifraud system.
        const deviceSessionId = window.OpenPay.deviceData.setup('paygw_openpay_card_form');
        debug('Device session ID generated', deviceSessionId);

        const fields = {
            holder: root.querySelector('#paygw_openpay_holder_name'),
            card: root.querySelector('#paygw_openpay_card_number'),
            cardDisplay: root.querySelector('#paygw_openpay_card_number_display'),
            expmonth: root.querySelector('#paygw_openpay_expiration_month'),
            expyear: root.querySelector('#paygw_openpay_expiration_year'),
            cvv: root.querySelector('#paygw_openpay_cvv2'),
        };

        const digitsOnly = (value) => (value || '').replace(/\D/g, '');
        const formatCardNumber = (value) => digitsOnly(value).slice(0, 19)
            .replace(/(.{4})/g, '$1 ')
            .trim();
        const clampTwoDigits = (value) => digitsOnly(value).slice(0, 2);
        const clampYear = (value) => digitsOnly(value).slice(0, 4);
        const clampCvv = (value) => digitsOnly(value).slice(0, 4);

        const getFeedbackEl = (input) => {
            if (!input) {
                return null;
            }
            return root.querySelector(`[data-error-for="${input.id}"]`);
        };

        const setFieldError = (input, message) => {
            if (!input) {
                return;
            }
            const feedback = getFeedbackEl(input);
            if (message) {
                input.classList.add('is-invalid');
                if (feedback) {
                    feedback.textContent = message;
                    feedback.style.display = 'block';
                }
            } else {
                input.classList.remove('is-invalid');
                if (feedback) {
                    feedback.textContent = '';
                    feedback.style.display = '';
                }
            }
        };

        const normalizeYear = (value) => {
            const numeric = clampYear(value);
            if (numeric.length <= 2) {
                return numeric;
            }
            // Keep last two digits if user types full year.
            return numeric.slice(-2);
        };

        const syncCardValue = () => {
            if (fields.card && fields.cardDisplay) {
                fields.card.value = digitsOnly(fields.cardDisplay.value);
            }
        };

        const validators = {
            holder: (value) => {
                const valid = value && value.trim().length >= 3;
                return valid ? '' : validationMessages.holder;
            },
            card: (value) => {
                const digits = digitsOnly(value);
                const valid = digits.length >= 13 && digits.length <= 19;
                return valid ? '' : validationMessages.card;
            },
            expmonth: (value) => {
                if (!value) {
                    return validationMessages.expmonth;
                }
                const numeric = parseInt(value, 10);
                const valid = numeric >= 1 && numeric <= 12;
                return valid ? '' : validationMessages.expmonth;
            },
            expyear: (value) => {
                if (!value) {
                    return validationMessages.expyear;
                }
                const numeric = parseInt(value, 10);
                if (Number.isNaN(numeric)) {
                    return validationMessages.expyear;
                }
                const now = new Date();
                const currentYear = now.getFullYear() % 100;
                const currentMonth = now.getMonth() + 1;
                const fullYear = numeric >= 0 && numeric <= 99 ? numeric : numeric % 100;
                if (fullYear < currentYear) {
                    return validationMessages.expyear;
                }
                if (fullYear === currentYear) {
                    const monthVal = parseInt(fields.expmonth.value, 10);
                    if (!Number.isNaN(monthVal) && monthVal < currentMonth) {
                        return validationMessages.expyear;
                    }
                }
                return '';
            },
            cvv: (value) => {
                const digits = digitsOnly(value);
                const valid = digits.length === 3 || digits.length === 4;
                return valid ? '' : validationMessages.cvv;
            },
        };

        const runValidation = (fieldName, showErrors = false) => {
            let input = fields[fieldName];
            if (fieldName === 'card' && fields.cardDisplay) {
                input = fields.cardDisplay;
            }
            if (!input) {
                return true;
            }
            let value = input.value.trim();
            if (fieldName === 'card') {
                syncCardValue();
                value = fields.card ? fields.card.value : value;
            }
            const message = validators[fieldName](value);
            if (showErrors || input.dataset.touched === 'true') {
                setFieldError(input, message);
            } else if (!message) {
                setFieldError(input, '');
            }
            return !message;
        };

        const validateAll = (showErrors = false) => {
            return ['holder', 'card', 'expmonth', 'expyear', 'cvv']
                .map((name) => runValidation(name, showErrors))
                .every(Boolean);
        };

        const updateSubmitState = () => {
            const valid = validateAll(false);
            if (payBtn) {
                payBtn.disabled = !valid;
            }
        };

        const markTouched = (input) => {
            if (input) {
                input.dataset.touched = 'true';
            }
        };

        if (fields.holder) {
            fields.holder.addEventListener('input', () => {
                updateSubmitState();
                if (fields.holder.dataset.touched === 'true') {
                    runValidation('holder', true);
                }
            });
            fields.holder.addEventListener('blur', () => {
                markTouched(fields.holder);
                runValidation('holder', true);
                updateSubmitState();
            });
        }

        if (fields.cardDisplay) {
            fields.cardDisplay.addEventListener('input', () => {
                const prev = digitsOnly(fields.cardDisplay.value);
                fields.cardDisplay.value = formatCardNumber(prev);
                syncCardValue();
                updateSubmitState();
                if (fields.cardDisplay.dataset.touched === 'true') {
                    runValidation('card', true);
                }
            });
            fields.cardDisplay.addEventListener('blur', () => {
                markTouched(fields.cardDisplay);
                syncCardValue();
                runValidation('card', true);
                updateSubmitState();
            });
        }

        if (fields.expmonth) {
            fields.expmonth.addEventListener('input', () => {
                fields.expmonth.value = clampTwoDigits(fields.expmonth.value);
                if (fields.expmonth.dataset.touched === 'true') {
                    runValidation('expmonth', true);
                }
                updateSubmitState();
            });
            fields.expmonth.addEventListener('blur', () => {
                markTouched(fields.expmonth);
                if (fields.expmonth.value.length === 1) {
                    fields.expmonth.value = `0${fields.expmonth.value}`;
                }
                runValidation('expmonth', true);
                updateSubmitState();
            });
        }

        if (fields.expyear) {
            fields.expyear.addEventListener('input', () => {
                fields.expyear.value = normalizeYear(fields.expyear.value);
                if (fields.expyear.dataset.touched === 'true') {
                    runValidation('expyear', true);
                }
                updateSubmitState();
            });
            fields.expyear.addEventListener('blur', () => {
                markTouched(fields.expyear);
                fields.expyear.value = normalizeYear(fields.expyear.value);
                runValidation('expyear', true);
                updateSubmitState();
            });
        }

        if (fields.cvv) {
            fields.cvv.addEventListener('input', () => {
                fields.cvv.value = clampCvv(fields.cvv.value);
                if (fields.cvv.dataset.touched === 'true') {
                    runValidation('cvv', true);
                }
                updateSubmitState();
            });
            fields.cvv.addEventListener('blur', () => {
                markTouched(fields.cvv);
                runValidation('cvv', true);
                updateSubmitState();
            });
        }

        const cleanup = () => {
            if (cancelBtn) {
                cancelBtn.disabled = false;
            }
            const payBtn = root.querySelector('[data-action="pay"]');
            if (payBtn) {
                payBtn.disabled = false;
            }
        };

        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => {
                debug('User cancelled modal');
                modal.hide();
            });
        }

        return await new Promise((resolve, reject) => {
            form.addEventListener('submit', async(e) => {
                e.preventDefault();

                if (payBtn) {
                    payBtn.disabled = true;
                }
                if (cancelBtn) {
                    cancelBtn.disabled = true;
                }

                try {
                    if (!validateAll(true)) {
                        setStatus(modal, validationMessages.form, true);
                        cleanup();
                        reject(validationMessages.form);
                        return;
                    }

                    debug('Submitting card form');
                    setStatus(modal, await getString('processingpayment', 'paygw_openpay'), false);

                    // Temporarily strip card number formatting before tokenization.
                    let originalCardDisplayValue = null;
                    if (fields.cardDisplay) {
                        originalCardDisplayValue = fields.cardDisplay.value;
                        fields.cardDisplay.value = digitsOnly(fields.cardDisplay.value);
                        syncCardValue();
                    }

                    const tokenResponse = await new Promise((res, rej) => {
                        window.OpenPay.token.extractFormAndCreate(
                            'paygw_openpay_card_form',
                            (response) => {
                                debug('Token created', response?.data?.id);
                                res(response);
                            },
                            (err) => {
                                debug('Tokenization error', err);
                                rej(err);
                            }
                        );
                    }).finally(() => {
                        if (fields.cardDisplay && originalCardDisplayValue !== null) {
                            fields.cardDisplay.value = formatCardNumber(originalCardDisplayValue);
                            syncCardValue();
                        }
                    });

                    const tokenId = tokenResponse?.data?.id;
                    if (!tokenId) {
                        throw new Error('Token ID missing');
                    }

                    const cardholder = root.querySelector('#paygw_openpay_holder_name')?.value || '';

                    debug('Calling createCharge', {tokenId, deviceSessionId});
                    const result = await Repository.createCharge(
                        component,
                        paymentArea,
                        itemId,
                        tokenId,
                        deviceSessionId,
                        cardholder
                    );
                    debug('Charge result', result);

                    modal.hide();
                    resolve(result.message);
                } catch (err) {
                    // Help diagnose unexpected error shapes.
                    window.console?.error?.('[paygw_openpay] Payment error', err);
                    const msg = getErrorMessage(err);
                    try {
                        setStatus(modal, await getString('paymentfailed', 'paygw_openpay', msg), true);
                    } catch (e2) {
                        setStatus(modal, msg, true);
                    }
                    cleanup();
                    reject(msg);
                }
            });
        });
    } catch (err) {
        window.console?.error?.('[paygw_openpay] Setup error', err);
        throw getErrorMessage(err);
    }
};
