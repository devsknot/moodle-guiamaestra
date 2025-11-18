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

namespace local_wb_news\external;

use context_system;
use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

/**
 * External API for getting news list.
 *
 * @package     local_wb_news
 * @copyright   2025 GuiaMaestra
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_news extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'instanceid' => new external_value(PARAM_INT, 'ID de la instancia de noticias', VALUE_REQUIRED),
        ]);
    }

    /**
     * Get news list for a given instance.
     *
     * @param int $instanceid Instance ID
     * @return array News items
     */
    public static function execute($instanceid): array {
        global $PAGE;

        $params = self::validate_parameters(self::execute_parameters(), [
            'instanceid' => $instanceid,
        ]);

        $context = context_system::instance();
        $PAGE->set_context($context);
        self::validate_context($context);

        try {
            $news = \local_wb_news\news::getinstance($params['instanceid']);
            $items = $news->return_list_of_news();

            return [
                'items' => $items,
                'success' => true,
                'message' => '',
            ];
        } catch (\Throwable $e) {
            return [
                'items' => [],
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Returns description of method result value.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'items' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'News ID'),
                    'instanceid' => new external_value(PARAM_INT, 'Instance ID'),
                    'active' => new external_value(PARAM_INT, 'Active status', VALUE_OPTIONAL),
                    'imagemode' => new external_value(PARAM_INT, 'Image mode (header/background)', VALUE_OPTIONAL),
                    'sortorder' => new external_value(PARAM_INT, 'Sort order', VALUE_OPTIONAL),
                    'bgimage' => new external_value(PARAM_RAW, 'Background image URL', VALUE_OPTIONAL),
                    'bgimagetext' => new external_value(PARAM_TEXT, 'Background image alt text', VALUE_OPTIONAL),
                    'icon' => new external_value(PARAM_RAW, 'Icon URL', VALUE_OPTIONAL),
                    'icontext' => new external_value(PARAM_TEXT, 'Icon alt text', VALUE_OPTIONAL),
                    'bgcolor' => new external_value(PARAM_TEXT, 'Background color (hex)', VALUE_OPTIONAL),
                    'userid' => new external_value(PARAM_INT, 'Author user ID'),
                    'headline' => new external_value(PARAM_TEXT, 'News headline'),
                    'subheadline' => new external_value(PARAM_TEXT, 'News subheadline', VALUE_OPTIONAL),
                    'description' => new external_value(PARAM_RAW, 'Formatted description'),
                    'descriptionformat' => new external_value(PARAM_INT, 'Description format', VALUE_OPTIONAL),
                    'btnlink' => new external_value(PARAM_URL, 'Button link URL', VALUE_OPTIONAL),
                    'btnlinkattributes' => new external_value(PARAM_TEXT, 'Button link attributes', VALUE_OPTIONAL),
                    'btntext' => new external_value(PARAM_TEXT, 'Button text', VALUE_OPTIONAL),
                    'lightmode' => new external_value(PARAM_INT, 'Light mode variant', VALUE_OPTIONAL),
                    'cssclasses' => new external_value(PARAM_TEXT, 'Additional CSS classes', VALUE_OPTIONAL),
                    'json' => new external_value(PARAM_RAW, 'Additional JSON data', VALUE_OPTIONAL),
                    'timecreated' => new external_value(PARAM_INT, 'Creation timestamp'),
                    'timemodified' => new external_value(PARAM_INT, 'Modification timestamp'),
                    'author' => new external_value(PARAM_TEXT, 'Author name', VALUE_OPTIONAL),
                    'authorpicture' => new external_value(PARAM_URL, 'Author picture URL', VALUE_OPTIONAL),
                    'detailurl' => new external_value(PARAM_URL, 'Detail page URL', VALUE_OPTIONAL),
                ], 'News item')
            ),
            'success' => new external_value(PARAM_BOOL, 'Operation success status'),
            'message' => new external_value(PARAM_TEXT, 'Error message if any'),
        ]);
    }
}
