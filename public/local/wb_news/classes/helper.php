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

namespace local_wb_news;


defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/filelib.php');

use stdClass;
use core_course\external\course_summary_exporter;


/**
 * Class news.
 * @package local_wb_news
 * @author Thomas Winkler
 * @copyright 2024 Wunderbyte GmbH
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {
    /**
     * Returns the URL of the image associated with the given course ID,
     * or a placeholder image URL if no image is associated with the course.
     *
     * @param stdclass $course The course record
     * @return string The URL of the image associated with the item.
     */
    public static function get_course_image($course) {

        $courseimage = course_summary_exporter::get_course_image((object)$course);
        return $courseimage;
    }
}
