<?php
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
 * Openpay payment gateway definition.
 *
 * @package    paygw_openpay
 * @copyright  2025 GuiaMaestra
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace paygw_openpay;

use core_payment\form\account_gateway;

/**
 * The gateway class for Openpay payment gateway.
 */
class gateway extends \core_payment\gateway {

    /**
     * Openpay Colombia processes payments in COP.
     *
     * @return string[]
     */
    public static function get_supported_currencies(): array {
        return ['COP'];
    }

    /**
     * Adds configuration fields to the payment account form.
     *
     * @param account_gateway $form
     */
    public static function add_configuration_to_gateway_form(account_gateway $form): void {
        $mform = $form->get_mform();

        $mform->addElement('text', 'merchantid', get_string('merchantid', 'paygw_openpay'));
        $mform->setType('merchantid', PARAM_TEXT);
        $mform->addHelpButton('merchantid', 'merchantid', 'paygw_openpay');

        $mform->addElement('text', 'publickey', get_string('publickey', 'paygw_openpay'));
        $mform->setType('publickey', PARAM_TEXT);
        $mform->addHelpButton('publickey', 'publickey', 'paygw_openpay');

        $mform->addElement('passwordunmask', 'secretkey', get_string('secretkey', 'paygw_openpay'));
        $mform->setType('secretkey', PARAM_TEXT);
        $mform->addHelpButton('secretkey', 'secretkey', 'paygw_openpay');

        $environmentoptions = [
            'sandbox' => get_string('environment_sandbox', 'paygw_openpay'),
            'production' => get_string('environment_production', 'paygw_openpay'),
        ];
        $mform->addElement('select', 'environment', get_string('environment', 'paygw_openpay'), $environmentoptions);
        $mform->addHelpButton('environment', 'environment', 'paygw_openpay');
        $mform->setDefault('environment', 'sandbox');

        // Country is fixed to CO for this integration but stored for completeness.
        $mform->addElement('text', 'country', get_string('country', 'paygw_openpay'));
        $mform->setType('country', PARAM_TEXT);
        $mform->addHelpButton('country', 'country', 'paygw_openpay');
        $mform->setDefault('country', 'CO');

        $mform->addElement('text', 'webhooktoken', get_string('webhooktoken', 'paygw_openpay'));
        $mform->setType('webhooktoken', PARAM_TEXT);
        $mform->addHelpButton('webhooktoken', 'webhooktoken', 'paygw_openpay');
    }

    /**
     * Validates configuration.
     *
     * @param account_gateway $form
     * @param \stdClass $data
     * @param array $files
     * @param array $errors
     */
    public static function validate_gateway_form(account_gateway $form, \stdClass $data, array $files, array &$errors): void {
        if ($data->enabled &&
            (empty($data->merchantid) || empty($data->publickey) || empty($data->secretkey))) {
            $errors['enabled'] = get_string('gatewaycannotbeenabled', 'paygw_openpay');
        }
    }
}
