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
 * Contains class mod_questionnaire\output\indexpage
 *
 * @package    local_wb_news
 * @copyright  2024 Wunderbyte Gmbh <info@wunderbyte.at>
 * @author     Georg Maißer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

namespace local_wb_news\output;

use local_wb_news\news;
use renderable;
use renderer_base;
use templatable;

/**
 * viewtable class to display view.php
 * @package local_wb_news
 *
 */
class newsview implements renderable, templatable {
    /**
     * Instanceid, 0 for all items
     *
     * @var int
     */
    private $id = 0;

    /**
     * Newsitem as array
     *
     * @var array
     */
    private $newsitem = [];

    /**
     * Constructor.
     *
     * @param int $id = 0;
     * @param int $instanceid = 0;
     */
    public function __construct(int $id, int $instanceid) {

        global $DB;

        $this->id = $id;

        $news = news::getinstance($instanceid);
        $newsitem = (array)$news->get_formatted_news_item($id);

        $user = \core_user::get_user($newsitem['userid']);

        $newsitem['firstname'] = $user->firstname;
        $newsitem['lastname'] = $user->lastname;

        $newsitem['headline'] = format_string($newsitem['headline']);
        $newsitem['subheadline'] = format_string($newsitem['subheadline']);
        $newsitem['description'] = format_text($newsitem['description']);
        $newsitem['btntext'] = format_string($newsitem['btntext']);
        $newsitem['btnlink'] = format_string($newsitem['btnlink']);
        $newsitem['bgimagetext'] = isset($newsitem['bgimagetext']) ? format_string($newsitem['bgimagetext']) : false;
        $newsitem['headerimagetext'] = isset($newsitem['headerimagetext']) ? format_string($newsitem['headerimagetext']) : false;
        $newsitem['icontext'] = format_string($newsitem['icontext']);

        $this->newsitem = $newsitem;
    }

    /**
     * Return headline of newsitem.
     *
     * @return string
     *
     */
    public function return_headline() {
        return $this->newsitem['headine'] ?? '';
    }

    /**
     * Prepare data for use in a template
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output) {

        return $this->newsitem;
    }
}
