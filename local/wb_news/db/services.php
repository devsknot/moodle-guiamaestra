<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Web service definitions for local_wb_news plugin.
 *
 * @package     local_wb_news
 * @copyright   2025 GuiaMaestra
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_wb_news_get_news' => [
        'classname'   => 'local_wb_news\\external\\get_news',
        'description' => 'Returns the news entries for a given instance.',
        'type'        => 'read',
        'ajax'        => true,
        'capabilities'=> '',
        'services'    => ['wb_news_service'],
    ],
];

$services = [
    'wb_news_service' => [
        'functions' => ['local_wb_news_get_news'],
        'restrictedusers' => 0,
        'enabled' => 1,
        'shortname' => 'wb_news_service',
        'downloadfiles' => 0,
        'uploadfiles' => 0,
    ],
];
