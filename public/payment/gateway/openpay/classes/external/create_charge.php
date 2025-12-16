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
 * Creates Openpay charges using a token generated via Openpay.js.
 *
 * @package    paygw_openpay
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace paygw_openpay\external;

use coding_exception;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use core_payment\helper;
use invalid_parameter_exception;
use moodle_exception;
use paygw_openpay\gateway;
use paygw_openpay\payable_description;

/**
 * Handles token-based card charges.
 */
class create_charge extends external_api {

    /**
     * Parameter definition.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'component' => new external_value(PARAM_COMPONENT, 'Component name'),
            'paymentarea' => new external_value(PARAM_AREA, 'Payment area in the component'),
            'itemid' => new external_value(PARAM_INT, 'Payment item ID'),
            'tokenid' => new external_value(PARAM_RAW, 'Token ID returned by Openpay.js'),
            'devicesessionid' => new external_value(PARAM_RAW, 'Device session ID for antifraud', VALUE_OPTIONAL, ''),
            'cardholder' => new external_value(PARAM_TEXT, 'Cardholder name', VALUE_OPTIONAL, ''),
        ]);
    }

    /**
     * Creates the Openpay charge and completes the Moodle payment.
     *
     * @param string $component
     * @param string $paymentarea
     * @param int $itemid
     * @param string $tokenid
     * @param string $devicesessionid
     * @param string $cardholder
     * @return array
     * @throws moodle_exception
     */
    public static function execute(string $component, string $paymentarea, int $itemid, string $tokenid,
            string $devicesessionid = '', string $cardholder = ''): array {
        global $DB, $USER, $CFG;

        error_log("[paygw_openpay] create_charge::execute() called with component={$component}, paymentarea={$paymentarea}, itemid={$itemid}, tokenid={$tokenid}");

        require_once($CFG->libdir . '/filelib.php');
        require_once($CFG->libdir . '/weblib.php');

        self::validate_parameters(self::execute_parameters(), [
            'component' => $component,
            'paymentarea' => $paymentarea,
            'itemid' => $itemid,
            'tokenid' => $tokenid,
            'devicesessionid' => $devicesessionid,
            'cardholder' => $cardholder,
        ]);

        $config = helper::get_gateway_configuration($component, $paymentarea, $itemid, 'openpay');
        if (empty($config['merchantid']) || empty($config['publickey']) || empty($config['secretkey'])) {
            throw new coding_exception('Openpay gateway is not fully configured.');
        }

        $payable = helper::get_payable($component, $paymentarea, $itemid);
        $currency = $payable->get_currency();
        if (!in_array($currency, gateway::get_supported_currencies(), true)) {
            throw new invalid_parameter_exception('Unsupported currency for Openpay: ' . $currency);
        }

        $surcharge = helper::get_gateway_surcharge('openpay');
        $amount = helper::get_rounded_cost($payable->get_amount(), $currency, $surcharge);

        $description = payable_description::describe($component, $paymentarea, $itemid);

        $payload = self::build_charge_payload(
            $config,
            $tokenid,
            $devicesessionid,
            $amount,
            $currency,
            $description,
            $cardholder
        );
        $response = self::send_charge_request($config, $payload);

        if (empty($response['id']) || ($response['status'] ?? '') !== 'completed') {
            $status = $response['status'] ?? 'unknown';
            $message = $response['error_message'] ?? $status;
            throw new moodle_exception('paymentfailed', 'paygw_openpay', '', $message);
        }

        $paymentid = helper::save_payment($payable->get_account_id(), $component, $paymentarea, $itemid,
            (int) $USER->id, $amount, $currency, 'openpay');

        $record = (object) [
            'paymentid' => $paymentid,
            'chargeid' => $response['id'],
            'rawresponse' => json_encode($response),
            'timecreated' => time(),
        ];
        $DB->insert_record('paygw_openpay', $record);

        helper::deliver_order($component, $paymentarea, $itemid, $paymentid, (int) $USER->id);

        return [
            'success' => true,
            'message' => get_string('paymentcompleted', 'paygw_openpay'),
        ];
    }

    /**
     * Result schema.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Charge completed.'),
            'message' => new external_value(PARAM_RAW, 'Message for the user.'),
        ]);
    }

    /**
     * Builds the payload required by Openpay.
     *
     * @param array $config
     * @param string $tokenid
     * @param string $devicesessionid
     * @param float $amount
     * @param string $currency
     * @param string $description
     * @param string $cardholder
     * @return array
     */
    protected static function build_charge_payload(array $config, string $tokenid, string $devicesessionid,
            float $amount, string $currency, string $description, string $cardholder): array {
        global $USER;

        $customername = trim($cardholder) ?: fullname($USER);
        $customer = [
            'name' => $customername,
            'last_name' => '',
            'email' => $USER->email,
            'phone_number' => $USER->phone1 ?? '',
        ];

        $payload = [
            'method' => 'card',
            'source_id' => $tokenid,
            'amount' => $amount,
            'currency' => $currency,
            'description' => mb_substr(format_string($description, true, ['context' => \context_system::instance()]), 0, 250),
            'order_id' => self::build_order_id(),
            'customer' => array_filter($customer),
        ];

        if (!empty($devicesessionid)) {
            $payload['device_session_id'] = $devicesessionid;
        }

        return $payload;
    }

    /**
     * Sends the HTTP request to Openpay.
     *
     * @param array $config
     * @param array $payload
     * @return array
     */
    protected static function send_charge_request(array $config, array $payload): array {
        $environment = $config['environment'] ?? 'sandbox';
        $baseurl = $environment === 'production'
            ? 'https://api.openpay.co/v1/'
            : 'https://sandbox-api.openpay.co/v1/';
        $endpoint = $baseurl . $config['merchantid'] . '/charges';

        $jsonpayload = json_encode($payload);
        error_log("[paygw_openpay] Sending charge to: {$endpoint}");
        error_log("[paygw_openpay] Payload: {$jsonpayload}");

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $jsonpayload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Basic ' . base64_encode($config['secretkey'] . ':'),
            ],
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlerror = curl_error($ch);
        curl_close($ch);

        error_log("[paygw_openpay] HTTP code: {$httpcode}");
        error_log("[paygw_openpay] Response: {$response}");
        if ($curlerror) {
            error_log("[paygw_openpay] Curl error: {$curlerror}");
        }

        $decoded = json_decode($response, true);

        if ($response === false || $httpcode >= 400) {
            $errormsg = $curlerror ?: $response;

            if (is_array($decoded)) {
                $description = $decoded['description'] ?? '';
                $errorcode = $decoded['error_code'] ?? '';
                $requestid = $decoded['request_id'] ?? '';
                $parts = array_filter([
                    $description ?: ($decoded['message'] ?? ''),
                    $errorcode ? "code {$errorcode}" : '',
                    $requestid ? "request {$requestid}" : '',
                ]);
                if ($parts) {
                    $errormsg = implode(' | ', $parts);
                }
            }

            throw new moodle_exception('paymentfailed', 'paygw_openpay', '', $errormsg);
        }

        if (!is_array($decoded)) {
            throw new moodle_exception('paymentfailed', 'paygw_openpay', '', 'Invalid response from Openpay');
        }

        return $decoded;
    }

    /**
     * Generates a unique order ID.
     *
     * @return string
     */
    protected static function build_order_id(): string {
        return 'mdl-' . time() . '-' . random_int(1000, 9999);
    }
}
