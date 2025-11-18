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
 * Entitiesrelation form implemantion to use entities in other plugins
 * @package     local_wb_news
 * @copyright   2023 Wunderbyte GmbH
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_wb_news\form;
use local_wb_news\news;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once("$CFG->libdir/formslib.php");

use context;
use core_form\dynamic_form;
use moodle_url;
use context_system;

/**
 * Add file form.
 * @copyright Wunderbyte GmbH <info@wunderbyte.at>
 * @author Thomas Winkler
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class addeditinstancemodal extends dynamic_form {
    /**
     * {@inheritdoc}
     * @see moodleform::definition()
     */
    public function definition() {
        $mform = $this->_form;
        $customdata = $this->_ajaxformdata;

        $mform = $this->_form; // Don't forget the underscore!

        // ID is here instanceid.
        $mform->addElement('hidden', 'id', $customdata['id'] ?? 0);
        $mform->setType('id', PARAM_INT);

        // Add headline field.
        $mform->addElement('text', 'name', get_string('name', 'local_wb_news'));
        $mform->setType('name', PARAM_TEXT);

        $options = [
            'local_wb_news/wb_news_masonry' => get_string('masonrytemplate', 'local_wb_news'),
            'local_wb_news/wb_news_slider' => get_string('slidertemplate', 'local_wb_news'),
            'local_wb_news/wb_news_tabs' => get_string('tabstemplate', 'local_wb_news'),
            'local_wb_news/wb_news_grid' => get_string('gridtemplate', 'local_wb_news'),
            'local_wb_news/wb_news_blog' => get_string('blogtemplate', 'local_wb_news'),
            'local_wb_news/wb_news_vertical_blog' => get_string('verticalblogtemplate', 'local_wb_news'),
            'local_wb_news/wb_news_infoslides' => get_string('infoslidestemplate', 'local_wb_news'),
            'local_wb_news/wb_news_image_column' => get_string('imagecolumntemplate', 'local_wb_news'),
            'local_wb_news/wb_news_crosslinks' => get_string('crosslinkstemplate', 'local_wb_news'),
            'local_wb_news/wb_news_timeline' => get_string('timelinetemplate', 'local_wb_news'),
            'local_wb_news/wb_news_timeline2' => get_string('timelinetemplate2', 'local_wb_news'),
            'local_wb_news/wb_news_pdfgallery' => get_string('pdfgallery', 'local_wb_news'),
            'local_wb_news/wb_news_imageseparator' => get_string('imgseparator', 'local_wb_news'),
        ];

        // Add subheadline field.
        $mform->addElement('select', 'template', get_string('template', 'local_wb_news'), $options);

        $options = [
            12 => 1,
            6 => 2,
            4 => 3,
            3 => 4,
            2 => 6,
            1 => 12,
        ];

        // Add subheadline field.
        $mform->addElement('select', 'columns', get_string('columns', 'local_wb_news'), $options);
        $mform->setDefault('columns', 4);

        $categories = news::get_contextid_options();

        $options = [
            'multiple' => true,
            'noselectionstring' => get_string('allareas', 'search'),
        ];

        $mform->addElement('autocomplete', 'contextids', get_string('alloweditincontext', 'local_wb_news'), $categories, $options);
    }

    /**
     * Check access for dynamic submission.
     *
     * @return void
     */
    protected function check_access_for_dynamic_submission(): void {
        // phpcs:ignore
        // TODO: capability to create advisors
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     *
     * This method can return scalar values or arrays that can be json-encoded, they will be passed to the caller JS.
     *
     * Submission data can be accessed as: $this->get_data()
     *
     * @return mixed
     */
    public function process_dynamic_submission() {
        global $CFG, $DB;

        $data = $this->get_data();

        $news = news::getinstance($data->instanceid ?? 0);
        $news->update_newsinstance($data);

        return $data;
    }

    /**
     * Load in existing data as form defaults
     *
     * Can be overridden to retrieve existing values from db by entity id and also
     * to preprocess editor and filemanager elements
     *
     * Example:
     *     $this->set_data(get_entity($this->_ajaxformdata['cmid']));
     */
    public function set_data_for_dynamic_submission(): void {
        $ajaxformdata = $this->_ajaxformdata;

        $id = $ajaxformdata['id'] ?? 0;
        $news = news::getinstance((int)$id);

        $data = new \stdClass();

        $data->id = $news->instanceid;
        $data->template = $news->return_template();
        $data->name = $news->return_name();
        $data->contextids = $news->return_contextids();
        $data->columns = $news->return_columns();

        $this->set_data($data);
    }

    /**
     * Returns form context
     *
     * If context depends on the form data, it is available in $this->_ajaxformdata or
     * by calling $this->optional_param()
     *
     * @return context
     */
    protected function get_context_for_dynamic_submission(): context {
        global $USER;
        return context_system::instance();
    }

    /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX
     *
     * This is used in the form elements sensitive to the page url, such as Atto autosave in 'editor'
     *
     * If the form has arguments (such as 'id' of the element being edited), the URL should
     * also have respective argument.
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        return new moodle_url('/local/wb_news/index.php');
    }

    /**
     * {@inheritdoc}
     * @see moodleform::validation()
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     */
    public function validation($data, $files) {

        $errors = [];

        if (empty($data['name'])) {
            $errors['name'] = get_string('noname', 'local_wb_news');
        }

        return $errors;
    }

    /**
     * {@inheritDoc}
     * @see moodleform::get_data()
     */
    public function get_data() {
        $data = parent::get_data();
        return $data;
    }
}
