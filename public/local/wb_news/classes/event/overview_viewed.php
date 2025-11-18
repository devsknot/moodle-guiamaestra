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
 *
 * @package local_wb_news
 * @copyright 2024 Thomas Winkler
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_wb_news\event;

/**
 * The news_created event class.
 *
 * @copyright 2024 Thomas Winkler
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class overview_viewed extends \core\event\base {
    /**
     * The init function
     */
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    /**
     * Returns the name
     *
     * @return string
     */
    public static function get_name() {
        return get_string('overviewviewed', 'local_wb_news');
    }

    /**
     * Returns the description
     *
     * @return string
     */
    public function get_description() {
        return "User with id '{$this->userid}' viewed the overview'.";
    }

    /**
     * Returns the moodle url
     *
     * @return string
     */
    public function get_url() {
        return new \moodle_url('/local/wb_news/index.php');
    }
}
