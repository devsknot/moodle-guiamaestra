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
 * Behat.
 *
 * @package    local_wb_news
 * @copyright 2025 Wunderbyte GmbH <info@wunderbyte.at>
 * @author Andrii Semenets
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Behat\Context\Step\Given;
use local_shopping_cart\local\cartstore;
use local_shopping_cart\shopping_cart;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Hook\Scope\AfterScenarioScope;

/**
 * Behat functions.
 *
 * Custom behat steps for local_wb_news.
 *
 * @copyright 2025 Wunderbyte GmbH <info@wunderbyte.at>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_local_wb_news extends behat_base {
    /**
     * Add a news instance to a Moodle Page as the content.
     *
     * @param string $instancename
     * @param string $pagename
     * @Given /^News instance "([^"]*)" has been added to the Page resource "([^"]*)"$/
     */
    public function i_add_news_instance_to_a_page(string $instancename, string $pagename) {
        global $DB;

        if (!$instanceid = $DB->get_field('local_wb_news_instance', 'id', ['name' => $instancename])) {
            throw new Exception('The specified news instance with name "' . $instancename . '" does not exist');
        }
        if (!$pagerecord = $DB->get_record('page', ['name' => $pagename], '*', MUST_EXIST)) {
            throw new Exception('The specified page instance with name "' . $pagename . '" does not exist');
        }
        $pagerecord->content = "[wbnews instance=" . $instanceid . "]";
        $DB->update_record('page', $pagerecord);
    }
}
