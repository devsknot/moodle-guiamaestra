<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Web service definitions for local_wb_news plugin.
 *
 * @package    local_wb_news
 * @copyright  2025 GuiaMaestra
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_wb_news_get_news' => [
        'classname'   => 'local_wb_news\external\get_news',
        'methodname'  => 'execute',
        'classpath'   => '',
        'description' => 'Devuelve el listado de noticias de una instancia específica',
        'type'        => 'read',
        'ajax'        => true,
        'capabilities'=> '', // Sin capability específica, acceso público
        'services'    => [MOODLE_OFFICIAL_MOBILE_SERVICE, 'wb_news_service'],
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
