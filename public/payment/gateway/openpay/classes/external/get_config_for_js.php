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
 * Returns configuration for the Openpay JS integration.
 *
 * @package    paygw_openpay
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace paygw_openpay\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use core_payment\helper;
use paygw_openpay\payable_description;

/**
 * Exposes configuration to the AMD module.
 */
class get_config_for_js extends external_api {

    /**
     * Params definition.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'component' => new external_value(PARAM_COMPONENT, 'Component name'),
            'paymentarea' => new external_value(PARAM_AREA, 'Payment area in the component'),
            'itemid' => new external_value(PARAM_INT, 'Payment item ID within the area'),
        ]);
    }

    /**
     * Returns the config values required by the Openpay frontend flow.
     *
     * @param string $component
     * @param string $paymentarea
     * @param int $itemid
     * @return array
     */
    public static function execute(string $component, string $paymentarea, int $itemid): array {
        self::validate_parameters(self::execute_parameters(), [
            'component' => $component,
            'paymentarea' => $paymentarea,
            'itemid' => $itemid,
        ]);

        $config = helper::get_gateway_configuration($component, $paymentarea, $itemid, 'openpay');
        $payable = helper::get_payable($component, $paymentarea, $itemid);
        $surcharge = helper::get_gateway_surcharge('openpay');

        $environment = $config['environment'] ?? 'sandbox';
        $sandbox = ($environment !== 'production');

        $description = '';
        try {
            $description = payable_description::describe($component, $paymentarea, $itemid);
        } catch (\Throwable $e) {
            error_log('[paygw_openpay] get_config_for_js describe() failed: ' . $e->getMessage());
        }

        return [
            'merchantid' => $config['merchantid'] ?? '',
            'publickey' => $config['publickey'] ?? '',
            'environment' => $environment,
            'sandbox' => $sandbox,
            'amount' => helper::get_rounded_cost($payable->get_amount(), $payable->get_currency(), $surcharge),
            'currency' => $payable->get_currency(),
            'description' => $description,
        ];
    }

    /**
     * Returns definition of result structure.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'merchantid' => new external_value(PARAM_TEXT, 'Merchant ID'),
            'publickey' => new external_value(PARAM_TEXT, 'Public key'),
            'environment' => new external_value(PARAM_TEXT, 'Configured environment'),
            'sandbox' => new external_value(PARAM_BOOL, 'Whether sandbox mode is active'),
            'amount' => new external_value(PARAM_FLOAT, 'Payable amount including surcharge'),
            'currency' => new external_value(PARAM_TEXT, 'Payable currency'),
            'description' => new external_value(PARAM_RAW, 'Payment description'),
        ]);
    }
}
