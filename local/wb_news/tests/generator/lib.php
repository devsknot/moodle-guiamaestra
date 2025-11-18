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

use local_wb_news\news;

/**
 * Class local_wb_news_generator for generation of dummy data
 *
 * @package local_wb_news
 * @category test
 * @copyright 2025 Wunderbyte Gmbh <info@wunderbyte.at>
 * @author Andrii Semenets
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_wb_news_generator extends testing_module_generator {
    /**
     * Function to create a dummy news instance.
     *
     * @param array|stdClass $record
     * @return stdClass the news instance object
     */
    public function create_news_instance($record = null) {
        global $DB;

        $record = (object) $record;

        // Converst array of names to context IDs.
        $record->contextids = [];
        $contexts = array_map('trim', explode(',', $record->contexts));
        $categories = news::get_contextid_options();
        foreach ($contexts as $context) {
            if (($contextid = array_search($context, $categories)) !== false) {
                $record->contextids[] = $contextid;
            }
        }

        // Create the news instance.
        $news = news::getinstance(0);
        $news->update_newsinstance($record);

        return $record;
    }

    /**
     * Function to create a dummy news item.
     *
     * @param array|stdClass $record
     * @return stdClass the news item object
     */
    public function create_news_item($record = null) {
        global $CFG, $USER, $DB;

        $record = (object) $record;

        // Required defaults.
        $record->subheadline = $record->subheadline ?? '';
        $record->description = $record->description ?? '';
        $record->descriptionformat = $record->descriptionformat ?? 0;
        $record->userid = $record->userid ?? $USER->id;
        $record->json = $record->json ?? '{}';
        $record->btnlinkattributes = $record->btnlinkattributes ?? '';

        $news = news::getinstance($record->instanceid);
        if (empty($record->id)) {
            // Such as headline is mandatory - we simply update news item to get actual ID.
            $record->id = $news->update_news($record);
        }

        $component = 'local_wb_news';
        $contextid = context_system::instance()->id;

        $fs = get_file_storage();

        if (!empty($record->bgimagefilepath)) {
            $record->bgimagefilepath = "{$CFG->dirroot}/{$record->bgimagefilepath}";
            // Create a dummy bgimage file.
            $filerecord = [
                'contextid' => $contextid,
                'component' => $component,
                'filearea' => 'bgimage',
                'itemid' => $record->id,
                'filepath' => '/',
                'filename' => basename($record->bgimagefilepath),
            ];
            if (file_exists($record->bgimagefilepath)) {
                $fs->create_file_from_pathname($filerecord, $record->bgimagefilepath);
            }
        }

        if (!empty($record->iconfilepath)) {
            $record->iconfilepath = "{$CFG->dirroot}/{$record->iconfilepath}";
            // Create a dummy bgimage file.
            $filerecord = [
                'contextid' => $contextid,
                'component' => $component,
                'filearea' => 'icon',
                'itemid' => $record->id,
                'filepath' => '/',
                'filename' => basename($record->iconfilepath),
            ];
            if (file_exists($record->iconfilepath)) {
                $fs->create_file_from_pathname($filerecord, $record->iconfilepath);
            }
        }

        $record->bgimage = null;
        // Save the URL for the bgimage.
        $files = $fs->get_area_files($contextid, $component, 'bgimage', $record->id);
        foreach ($files as $file) {
            $filename = $file->get_filename();
            if ($filename === '.') {
                continue;
            }

            $url = moodle_url::make_pluginfile_url(
                $file->get_contextid(),
                $file->get_component(),
                $file->get_filearea(),
                $file->get_itemid(),
                $file->get_filepath(),
                $file->get_filename()
            );

            $record->bgimage = $url->out();
        }

        // Save the URL for the icon.
        $files = $fs->get_area_files($contextid, $component, 'icon', $record->id);
        $record->icon = null;
        foreach ($files as $file) {
            $filename = $file->get_filename();
            if ($filename === '.') {
                continue;
            }

            $url = moodle_url::make_pluginfile_url(
                $file->get_contextid(),
                $file->get_component(),
                $file->get_filearea(),
                $file->get_itemid(),
                $file->get_filepath(),
                $file->get_filename()
            );

            $record->icon = $url->out();
        }

        $record->json = $record->json ?? '';

        $record->id = $news->update_news($record);

        return $record;
    }
}
