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
 * Language pack for Openpay payment gateway.
 *
 * @package    paygw_openpay
 * @copyright  2025 GuiaMaestra
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Openpay';
$string['pluginname_desc'] = 'Accept credit and debit card payments via Openpay (BBVA Colombia).';

$string['merchantid'] = 'Merchant ID';
$string['merchantid_help'] = 'Merchant identifier provided in the Openpay dashboard.';

$string['publickey'] = 'Public key';
$string['publickey_help'] = 'Public API key used on the frontend (Openpay.js).';

$string['secretkey'] = 'Private key';
$string['secretkey_help'] = 'Private API key used on the server to create charges.';

$string['environment'] = 'Environment';
$string['environment_sandbox'] = 'Sandbox';
$string['environment_production'] = 'Production';
$string['environment_help'] = 'Select the Openpay environment to use. Sandbox is recommended while testing.';

$string['country'] = 'Country';
$string['country_help'] = 'Country supported by this integration.';

$string['webhooktoken'] = 'Webhook verification code';
$string['webhooktoken_help'] = 'Store the verification code received from Openpay when registering the webhook.';

$string['gatewaycannotbeenabled'] = 'All Openpay credentials are required before enabling the gateway.';

$string['cardformtitle'] = 'Pay with card';
$string['cardinstructions'] = 'Enter the card details exactly as they appear on the card.';
$string['cardholdername'] = 'Cardholder name';
$string['cardnumber'] = 'Card number';
$string['expmonth'] = 'Expiration month';
$string['expyear'] = 'Expiration year';
$string['cvv'] = 'Security code (CVV)';
$string['error_holdername'] = 'Enter the cardholder name (at least 3 characters).';
$string['error_cardnumber'] = 'Enter a valid card number.';
$string['error_expiration_month'] = 'Use a valid month (01-12).';
$string['error_expiration_year'] = 'Use a valid future year.';
$string['error_cvv'] = 'Enter the 3 or 4 digit security code.';
$string['error_forminvalid'] = 'Please review the highlighted fields.';
$string['pay'] = 'Pay now';
$string['cancel'] = 'Cancel';
$string['processingpayment'] = 'Processing payment...';
$string['tokenizationfailed'] = 'Unable to tokenize the card: {$a}';
$string['paymentfailed'] = 'Payment failed: {$a}';
$string['paymentcompleted'] = 'Payment completed successfully.';
$string['defaultdescription'] = 'Payment for {$a->component}/{$a->paymentarea} item {$a->itemid}.';
$string['cartdescription'] = 'Payment for: {$a}';
$string['cartdescriptionmore'] = 'Payment for: {$a->courses} (+{$a->count} more)';
