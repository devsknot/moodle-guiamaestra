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
import {getString} from 'core/str';

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
        const [modal, config] = await Promise.all([
            showModalWithForm(),
            Repository.getConfigForJs(component, paymentArea, itemId),
        ]);
        debug('Config fetched', {component, paymentArea, itemId, config});

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

        // Generate device session ID for antifraud system.
        const deviceSessionId = window.OpenPay.deviceData.setup('paygw_openpay_card_form');
        debug('Device session ID generated', deviceSessionId);
        const cancelBtn = root.querySelector('[data-action="cancel"]');

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

                const payBtn = root.querySelector('[data-action="pay"]');
                if (payBtn) {
                    payBtn.disabled = true;
                }
                if (cancelBtn) {
                    cancelBtn.disabled = true;
                }

                try {
                    debug('Submitting card form');
                    setStatus(modal, await getString('processingpayment', 'paygw_openpay'), false);

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
