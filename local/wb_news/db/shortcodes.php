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
 * Shortcodes for local_wb_news
 *
 * @package local_wb_news
 * @subpackage db
 * @copyright 2024 Georg Maißer
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$shortcodes = [
    'wbnews' => [
        'callback' => 'local_wb_news\shortcodes::wbnews',
        'wraps' => false,
        'description' => 'wbnewslist',
    ],
    'wbnewscourse' => [
        'callback' => 'local_wb_news\shortcodes::wbnews_course',
        'wraps' => false,
        'description' => 'wbnewscourse',
    ],
    'wbnewsmycourses' => [
        'callback' => 'local_wb_news\shortcodes::wbnews_mycourses',
        'wraps' => false,
        'description' => 'wbnewscourse',
    ],
    'wbnewsavailablecourses' => [
        'callback' => 'local_wb_news\shortcodes::wbnews_availablecourses',
        'wraps' => false,
        'description' => 'wbnewscourse',
    ],
    'wbnewscompletedcourses' => [
        'callback' => 'local_wb_news\shortcodes::wbnews_completedcourses',
        'wraps' => false,
        'description' => 'wbnewscourse',
    ],
    'wbnewsinprogresscourses' => [
        'callback' => 'local_wb_news\shortcodes::wbnews_inprogresscourses',
        'wraps' => false,
        'description' => 'wbnewscourse',
    ],
];
