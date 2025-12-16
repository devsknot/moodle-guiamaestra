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

define('NO_DEBUG_DISPLAY', true);
define('NO_MOODLE_COOKIES', true);

require_once(__DIR__ . '/../../../config.php');

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);

if (is_array($payload)) {
    if (($payload['type'] ?? '') === 'verification' && !empty($payload['verification_code'])) {
        set_config('webhooktoken', $payload['verification_code'], 'paygw_openpay');
    }
}

http_response_code(200);
echo 'OK';
