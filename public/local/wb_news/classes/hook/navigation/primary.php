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

namespace local_wb_news\hook\navigation;

use core\hook\navigation\primary_extend as primary_hook;

defined('MOODLE_INTERNAL') || die();

/**
 * Hook listener for extending the primary navigation.
 */
class primary {
    /**
     * Extend the primary navigation using the existing helper.
     *
     * @param primary_hook $hook Moodle primary navigation hook instance.
     * @return void
     */
    public static function extend(primary_hook $hook): void {
        global $CFG;

        require_once($CFG->dirroot . '/local/wb_news/lib.php');

        $primaryview = $hook->get_primaryview();
        local_wb_news_extend_primary_navigation($primaryview);
    }
}
