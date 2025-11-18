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
use context_system;

/**
 * viewtable class to display view.php
 * @package local_wb_news
 *
 */
class wb_news implements renderable, templatable {
    /**
     * News items is the array used for output.
     *
     * @var array
     */
    private $news = [];

    /**
     * Instanceid, 0 for all items
     *
     * @var int
     */
    private $instanceid = 0;

    /**
     * Template
     *
     * @var string
     */
    private string $template = '';

    /**
     * Columns
     *
     * @var int
     */
    private int $columns = 4;

    /**
     * Constructor.
     *
     * @param int $instanceid = 0;
     */
    public function __construct(int $instanceid) {

        global $PAGE;

        $this->instanceid = $instanceid;

        $news = news::getinstance($instanceid);

        foreach ($news->return_list_of_news() as $newsitem) {
            $newsitem['editmode'] = $PAGE->user_is_editing();
            $this->news[] = $newsitem;
        }
        $this->template = $news->return_template();
        $this->columns = $news->return_columns();
    }

    /**
     * Returns the items of this class.
     *
     * @return array
     *
     */
    public function return_list() {

        global $PAGE;

        // If the instanceid here is 0, we return all of them.
        if (empty($this->instanceid)) {
            return [
                'instances' => news::return_all_instances(),
                'editmode' => has_capability('local/wb_news:manage', context_system::instance()),
            ];
        }

        $news = news::getinstance($this->instanceid);

        return [
            'instances' => [
                $news->return_instance(),
            ],
        ];
    }


    /**
     * Prepare data for use in a template
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output) {

        return $this->return_list();
    }
}
