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
 * @package    local_wb_news
 * @copyright  2024 Thomas Winkler
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Handles file requests for the 'local_wb_news' plugin.
 *
 * This function serves files from the 'bgimage' and 'icon' file areas of the 'local_wb_news' plugin.
 * It checks for the correct context, retrieves the requested file, and sends it to the client.
 *
 * @param stdClass $course The course object.
 * @param stdClass $cm The course module object.
 * @param stdClass $context The context object.
 * @param string $filearea The file area (expected values: 'bgimage', 'icon').
 * @param array $args Additional arguments (e.g., file path components).
 * @param bool $forcedownload Whether to force download.
 * @param array $options Additional options affecting file serving.
 * @return bool|void Returns false if any conditions are not met, otherwise sends the file.
 */
function local_wb_news_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    global $DB;
    // @codingStandardsIgnoreStart
    /*
    if ($context->contextlevel != CONTEXT_SYSTEM) {
        return false;
    }
    */

    //require_login();

    if ($filearea != 'bgimage' && $filearea != 'icon') {
        return false;
    }

    $itemid = (int)array_shift($args);

    /*
    if ($itemid != 0) {
     //   return false;
    }
    */

    $fs = get_file_storage();

    $filename = array_pop($args);
    if (empty($args)) {
        $filepath = '/';
    } else {
        $filepath = '/'.implode('/', $args).'/';
    }

    $file = $fs->get_file($context->id, 'local_wb_news', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false;
    }

    // finally send the file
    send_stored_file($file, 0, 0, true, $options); // download MUST be forced - security!
    // @codingStandardsIgnoreEnd
}

/**
 * Adds the NEWS entry to the primary navigation bar.
 *
 * @param \navigation_node $navigation
 * @return void
 */
function local_wb_news_extend_primary_navigation(\navigation_node $navigation): void {
    $context = \context_system::instance();

    if (!has_capability('local/wb_news:view', $context)) {
        return;
    }

    if ($navigation->find('local_wb_news_nav', \navigation_node::TYPE_CUSTOM)) {
        return;
    }

    $label = get_string('navnews', 'local_wb_news');
    $url = new \moodle_url('/local/wb_news/index.php');

    $node = \navigation_node::create(
        $label,
        $url,
        \navigation_node::TYPE_CUSTOM,
        null,
        'local_wb_news_nav'
    );
    $node->showinflatnavigation = true;

    if ($navigation->find('siteadmin', \navigation_node::TYPE_CONTAINER)) {
        $navigation->add_node($node, 'siteadmin');
        return;
    }

    $navigation->add_node($node);
}
