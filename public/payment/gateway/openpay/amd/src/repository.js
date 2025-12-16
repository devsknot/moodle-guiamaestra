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
 * Openpay repository module to encapsulate all of the AJAX requests.
 *
 * @module     paygw_openpay/repository
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';

export const getConfigForJs = (component, paymentArea, itemId) => {
    const request = {
        methodname: 'paygw_openpay_get_config_for_js',
        args: {
            component,
            paymentarea: paymentArea,
            itemid: itemId,
        },
    };

    return Ajax.call([request])[0];
};

export const createCharge = (component, paymentArea, itemId, tokenId, deviceSessionId, cardholder) => {
    const request = {
        methodname: 'paygw_openpay_create_charge',
        args: {
            component,
            paymentarea: paymentArea,
            itemid: itemId,
            tokenid: tokenId,
            devicesessionid: deviceSessionId || '',
            cardholder: cardholder || '',
        },
    };

    return Ajax.call([request])[0];
};
