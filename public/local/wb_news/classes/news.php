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
use core_tag_tag;
use local_wb_news\event\instance_created;
use local_wb_news\event\instance_deleted;
use local_wb_news\event\instance_updated;
use local_wb_news\event\news_created;
use local_wb_news\event\news_deleted;
use local_wb_news\event\news_updated;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/filelib.php');

use stdClass;
use context_system;

/**
 * Class news.
 * @package local_wb_news
 * @author Thomas Winkler
 * @copyright 2024 Wunderbyte GmbH
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class news {
    /**
     * IMAGEMODE_HEADER
     *
     * @var int
     */
    const IMAGEMODE_HEADER = 0;

    /**
     * IMAGEMODE_BACKGROUND
     *
     * @var int
     */
    const IMAGEMODE_BACKGROUND = 1;

    /**
     * Array of instances.
     *
     * @var array
     */
    private static array $instance = [];

    /**
     * [Description for $instanceid]
     *
     * @var int
     */
    public $instanceid = 0;

    /**
     * Array of news.
     *
     * @var array
     */
    private array $news = [];

    /**
     * Template string.
     *
     * @var string
     */
    private string $template = 'local_wb_news/wb_news_grid';

    /**
     * Name string.
     *
     * @var string
     */
    private string $name = '';

    /**
     * Contextids.
     *
     * @var string
     */
    private string $contextids = '';

    /**
     * Columns
     *
     * @var int
     */
    private int $columns = 4;


    /**
     * Constructor
     *
     * @param int $instanceid
     * @param bool $fetchitems
     */
    private function __construct(int $instanceid = 0, bool $fetchitems = true) {
        global $DB;

        $this->instanceid = $instanceid;
        // When there is no instance id, we fetch all the items from the start.
        if ($fetchitems) {
            $news = self::get_items_from_db($instanceid);

            if (count($news) < 1) {
                $instance = $DB->get_record('local_wb_news_instance', ['id' => $instanceid]);

                $this->name = $instance->name ?? 'noname';
                $this->template = $instance->template ?? 'notemplate';
                $this->columns = $instance->columns ?? 0;
                $this->contextids = $instance->contextids ?? '';
            }

            foreach ($news as $newsitem) {
                if ($newsitem->instanceid != $instanceid) {
                    $news = self::getinstance($newsitem->instanceid ?? 0, false);
                    $news->add_news($newsitem);
                } else {
                    $this->add_news($newsitem);
                }
            }
        }
    }

    /**
     * Get singelton instance.
     *
     * @param  int  $instanceid
     * @param  bool $fetchitems
     * @return news
     */
    public static function getinstance(int $instanceid, bool $fetchitems = true) {
        // Create the instance if it doesn't exist.
        if (!isset(self::$instance[$instanceid]) || self::$instance[$instanceid] === null) {
            self::$instance[$instanceid] = new self($instanceid, $fetchitems);
        }
        return self::$instance[$instanceid];
    }

    /**
     * Returns a list of news from the current instance.
     *
     * @return array
     *
     */
    public function return_list_of_news() {

        $returnarray = [];

        $isactive = false;
        foreach ($this->news as $news) {
            if (!empty($news->active)) {
                $isactive = true;
            }
            if (empty($news->userid)) {
                continue;
            }

            $item = (array)$this->get_formatted_news_item($news->id);
            $returnarray[] = $item;

            if ($item['active']) {
                $isactive = true;
            }
        }

        if (count($returnarray) === 1) {
            $returnarray[0]['dontshowarrows'] = true;
            $returnarray[0]['active'] = true;
        }

        if (!$isactive) {
            $returnarray[0]['active'] = true;
        }

        return $returnarray;
    }

    /**
     * Returns a the template string.
     *
     * @return string
     *
     */
    public function return_template() {
        return $this->template;
    }

    /**
     * Return array of contextids.
     *
     * @return array
     *
     */
    public function return_contextids() {

        global $DB;

        $contextids = explode(',', $this->contextids);

        $contextids = array_filter($contextids, fn($a) => !empty($a));

        return $contextids;
    }

    /**
     * Return number of columns in this instance.
     *
     * @return int
     *
     */
    public function return_columns() {

        return (int)$this->columns;
    }

    /**
     * Returns a the name string.
     *
     * @return string
     *
     */
    public function return_name() {
        return $this->name;
    }

    /**
     * Returns a specifc news item from the current instance.
     *
     * @param int $id
     * @return stdClass|null
     *
     */
    public function get_news_item($id) {

        $news = $this->news[$id] ?? null;

        return $news ?? null;
    }

    /**
     * Returns a specific news item formatted from the current instance.
     *
     * @param int $id
     * @return stdClass|null
     *
     */
    public function get_formatted_news_item($id) {

        global $PAGE;

        $news = $this->news[$id];

        $user = \core_user::get_user($news->userid);
        $context = \context_user::instance($news->userid);
        $url = moodle_url::make_pluginfile_url($context->id, 'user', 'icon', 0, '/', 'f1');
        $news->profileurl = $url->out();
        $news->fullname = "$user->firstname $user->lastname";
        $news->tags = array_values(core_tag_tag::get_item_tags_array('local_wb_news', 'news', $news->id));
        $news->publishedon = userdate($news->timecreated, get_string('strftimedate', 'core_langconfig'));
        $news->cssclasses = empty($news->cssclasses) ? false : $news->cssclasses;
        $news->headline = strip_tags(format_text($news->headline, FORMAT_HTML, ['noclean' => true]), '<br>');
        $news->subheadline = strip_tags(format_text($news->subheadline), '<br>');
        $news->btntext = strip_tags(format_text($news->btntext), '<br>');
        $news->btnlink = format_string($news->btnlink);
        $news->btnlink = htmlspecialchars_decode($news->btnlink, ENT_QUOTES);

        $attributes = explode(',', $news->btnlinkattributes ?? '');
        foreach ($attributes as $attribute) {
            if (strpos($attribute, '_') === 0) {
                $news->btnlinktarget[] = $attribute;
            } else {
                $news->btnlinkrel[] = $attribute;
            }
        }

        $news->description = format_text($news->description);
        if (!empty($news->bgimagetext)) {
            $news->bgimagetext = strip_tags(format_text($news->bgimagetext), '<br>');
        }
        if (!empty($news->headerimagetext)) {
            $news->headerimagetext = strip_tags(format_text($news->headerimagetext), '<br>');
        }
        $news->icontext = strip_tags(format_text($news->icontext), '<br>');

        $strippeddesc = strip_tags($news->description);
        if (strlen($strippeddesc) > 300) {
            $news->shortdescription = substr($strippeddesc, 0, 300) . '...';
        } else {
            $news->shortdescription = $strippeddesc;
        }

        if (isset($news->json) && $news->json) {
            $news->additionaldata = json_decode($news->json);
        }

        if (
            $this->template === "local_wb_news/wb_news_pdfgallery"
            && !empty($news->headerimage)
        ) {
            $news->pdflink = $news->headerimage;
        }

        $returntourl = $returnurl = $PAGE->url->out();

        $url = new moodle_url('/local/wb_news/newsview.php', [
            'id' => $news->id,
            'instanceid' => $this->instanceid,
            'returnto' => 'url',
            'returnurl' => $returnurl,
        ]);

        $news->detailviewurl = $url->out(false);

        return $news ?? null;
    }

    /**
     * Update or Create news
     *
     * @param stdClass $data
     * @return int $id
     */
    public function update_news($data) {
        global $DB, $USER;

        $id = $data->id ?? false;

        $data->userid = $USER->id;

        $data->fullname = "$USER->firstname $USER->lastname";
        $data->timemodified = time();

        // We need to keep our element intact.5.
        $insertdata = clone($data);
        // Unset all unwanted arrays.
        foreach ($insertdata as $key => $value) {
            if (is_array($value) || is_object($value)) {
                unset($insertdata->$key);
            }
        }

        if ($id) {
            $DB->update_record('local_wb_news', $insertdata, true);

            $event = news_updated::create([
                'context' => context_system::instance(),
                'userid' => $USER->id,
                'objectid' => $id,
            ]);

            $event->trigger();
        } else {
            $insertdata->timecreated = time();

            $id = $DB->insert_record('local_wb_news', $insertdata, true);

            $event = news_created::create([
                'context' => context_system::instance(),
                'userid' => $USER->id,
                'objectid' => $id,
            ]);

            $event->trigger();
        }

        $data->id = $id;

        if (isset($data->tags)) {
            core_tag_tag::set_item_tags('local_wb_news', 'local_wb_news', $id, context_system::instance(), $data->tags);
        }

        // We set active for 0 for every other record than our own.
        if (!empty($data->active)) {
            $records = $DB->get_records('local_wb_news', ['instanceid' => $data->instanceid]);
            foreach ($records as $record) {
                if ($record->id == $id) {
                    continue;
                }
                $record->active = 0;
                $DB->update_record('local_wb_news', $record);
            }
        }

        return $id;
    }

    /**
     * Delete news. On failure, return 0, else id of deleted record.
     *
     * @param stdClass $data
     */
    public function add_news(stdClass $data) {

        // Here, we apply the correct value depending on image mode.

        switch ($data->imagemode) {
            case self::IMAGEMODE_BACKGROUND:
                // Do nothing as we use the bgimage key already.
                break;
            case self::IMAGEMODE_HEADER:
                $data->headerimage = $data->bgimage;
                unset($data->bgimage);
                $data->headerimagetext = $data->bgimagetext;
                unset($data->bgimagetext);
                break;
        }

        $this->news[$data->id] = $data;
        if (!empty($data->template)) {
            $this->template = $data->template;
        }
        if (!empty($data->name) && empty($this->name)) {
            $this->name = strip_tags(format_text($data->name));
        }

        if (!empty($data->contextids) && empty($this->contextids)) {
            $this->contextids = $data->contextids;
        }

        $this->columns = empty($data->columns) ? $this->columns : $data->columns;
    }

    /**
     * Delete news. On failure, return 0, else id of deleted record.
     * We don't actually delete the record, but just make an update.
     * The instanceid is made negative, so instance 3 becomes -3. This allows later attribution.
     *
     * @param stdClass $data
     * @return int $id
     */
    public function delete_news($data) {
        global $DB, $USER;

        if (!empty($data->id)) {
            if ($record = $DB->get_record('local_wb_news', ['id' => $data->id])) {
                // Deleted news get the negative instanceid which they had so we can attribute them later.
                $record->instanceid = -$record->instanceid;
                $record->userid = $USER->id;
                $record->timemodified = time();

                $DB->update_record('local_wb_news', $record);

                $event = news_deleted::create([
                    'context' => context_system::instance(),
                    'userid' => $USER->id,
                    'objectid' => $data->id,
                ]);

                $event->trigger();

                return $data->id;
            }
        }

        return 0;
    }

    /**
     * Delete news. On failure, return 0, else id of deleted record.
     *
     * @param stdClass $data
     * @return int $id
     */
    public function copy_news($data) {
        global $DB, $USER;

        if (!empty($data->id)) {
            if ($record = $DB->get_record('local_wb_news', ['id' => $data->id])) {
                // Deleted news get the negative instanceid which they had so we can attribute them later.
                $record->userid = $USER->id;
                $record->timemodified = time();
                unset($record->id);

                $id = $DB->insert_record('local_wb_news', $record);

                return $id;
            }
        }

        return 0;
    }

    /**
     * Update or Create newsinstance
     *
     * @param stdClass $data
     * @return int $id
     */
    public function update_newsinstance($data) {
        global $DB, $USER;

        $id = $data->id ?? false;

        $data->userid = $USER->id;
        $data->timemodified = time();
        $data->name = empty($data->name) ? $id : $data->name;

        $data->contextids = implode(',', $data->contextids);

        if ($id) {
            $DB->update_record('local_wb_news_instance', $data, true);

            $event = instance_updated::create([
                'context' => context_system::instance(),
                'userid' => $USER->id,
                'objectid' => $data->id,
            ]);

            $event->trigger();

            return true;
        } else {
            $data->timecreated = time();
            $data->id = $DB->insert_record('local_wb_news_instance', $data, true);

            $event = instance_created::create([
                'context' => context_system::instance(),
                'userid' => $USER->id,
                'objectid' => $data->id,
            ]);

            $event->trigger();
        }

        return $id;
    }

    /**
     * Delete newsinstance. On failure, return 0, else id of deleted record.
     *
     * @param stdClass $data
     * @return int $id
     */
    public function delete_newsinstance($data) {
        global $DB, $USER;

        if (!empty($data->id)) {
            $DB->delete_records('local_wb_news_instance', ['id' => $data->id]);

            $event = instance_deleted::create([
                'context' => context_system::instance(),
                'userid' => $USER->id,
                'objectid' => $data->id,
            ]);

            $event->trigger();

            return $data->id;
        }

        return 0;
    }

    /**
     * Returns the instance as a renderable array.
     *
     * @return array
     *
     */
    public function return_instance() {

        global $PAGE;

        $instanceitem = [
            'instanceid' => $this->instanceid,
            'template' => $this->template,
            'columns' => $this->columns,
            'name' => $this->name,
            'contextids' => $this->contextids,
            'editmode' => (
                    $PAGE->user_is_editing()
                    || (strpos($PAGE->url, 'local/wb_news/index.php') !== false)
                ) && has_capability('local/wb_news:manage', context_system::instance()),
        ];

        if (!empty($this->news)) {
            $instanceitem['news'] = $this->return_list_of_news();
        }

        foreach ($instanceitem['news'] as $index => &$item) {
            $item['sliderindex'] = $index;
            if ($index == '0') {
                $item['slideactive'] = true;
            }
        }

        switch ($this->template) {
            case 'local_wb_news/wb_news_masonry':
                $instanceitem['masonrytemplate'] = true;
                break;
            case 'local_wb_news/wb_news_grid':
                $instanceitem['gridtemplate'] = true;
                break;
            case 'local_wb_news/wb_news_slider':
                $instanceitem['slidertemplate'] = true;
                break;
            case 'local_wb_news/wb_news_tabs':
                $instanceitem['tabstemplate'] = true;
                break;
            case 'local_wb_news/wb_news_blog':
                $instanceitem['blogtemplate'] = true;
                break;
            case 'local_wb_news/wb_news_image_column':
                $instanceitem['imagetemplate'] = true;
                break;
            case 'local_wb_news/wb_news_vertical_blog':
                $instanceitem['verticalblogtemplate'] = true;
                break;
            case 'local_wb_news/wb_news_infoslides':
                $instanceitem['infoslidestemplate'] = true;
                break;
            case 'local_wb_news/wb_news_crosslinks':
                $instanceitem['crosslinkstemplate'] = true;
                break;
            case 'local_wb_news/wb_news_timeline':
                $instanceitem['timelinetemplate'] = true;
                break;
            case 'local_wb_news/wb_news_pdfgallery':
                $instanceitem['pdfgallery'] = true;
                break;
            case 'local_wb_news/wb_news_timeline2':
                $instanceitem['timelinetemplate2'] = true;
                break;
            case 'local_wb_news/wb_news_imageseparator':
                $instanceitem['imageseparator'] = true;
                break;
        }

        return $instanceitem;
    }

    /**
     * Function will return an array of instances with names and ids.
     *
     * @return array
     *
     */
    public static function get_instance_options() {

        global $DB;

        $returnarray = [];

        $sql = "SELECT wni.id, wni.name
                FROM {local_wb_news_instance} wni";

        $instances = $DB->get_records_sql($sql);

        foreach ($instances as $instance) {
            $returnarray[$instance->id] = "$instance->name ($instance->id)";
        }

        return $returnarray;
    }

    /**
     * As we need it twice, we create a function.
     * @return array
     */
    public static function get_textfield_options() {

        $context = context_system::instance();

        return [
            'trusttext' => true,
            'subdirs' => true,
            'context' => $context,
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'noclean' => true,
        ];
    }

    /**
     * Fetch Items from DB.
     *
     * @param int $instanceid
     *
     * @return [type]
     *
     */
    private static function get_items_from_db(int $instanceid) {

        global $DB;

        $sql = $sql = "SELECT " . $DB->sql_concat(
            $DB->sql_cast_to_char('COALESCE(wni.id, 0)'),
            "'-'",
            $DB->sql_cast_to_char('COALESCE(wn.id, 0)')
        ) . " as ident, wn.*, wni.id as instanceid, wni.template, wni.name, wni.contextids, wni.columns
            FROM {local_wb_news} wn
            RIGHT JOIN {local_wb_news_instance} wni ON wni.id = wn.instanceid
            WHERE (wn.instanceid > 0 OR wn.instanceid IS NULL) "; // Deleted Items are not normally included in the results.

        if (!empty($instanceid)) {
            $params = ['instanceid' => $instanceid];
            $sql .= " AND wn.instanceid =:instanceid";
        } else {
            $params = [];
        }

        $sql .= " ORDER By wni.id ASC, wn.sortorder";

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Return all instances.
     *
     * @return array
     *
     */
    public static function return_all_instances() {

        global $PAGE;

        $returnarray = [];
        foreach (self::$instance as $instance) {
            if (empty($instance->instanceid)) {
                continue;
            }

            $instanceitem = $instance->return_instance();

            $returnarray[] = $instanceitem;
        }

        return $returnarray;
    }

    /**
     * Returns the possible options of a context select.
     *
     * @return array
     *
     */
    public static function get_contextid_options() {

        global $DB;

        $returnarray = [
            1 => get_string('system', 'local_wb_news'),
        ];

        $sql = "SELECT c.id, cc.name
                FROM {context} c
                JOIN {course_categories} cc ON c.instanceid=cc.id
                WHERE c.contextlevel = " . CONTEXT_COURSECAT;

        $records = $DB->get_records_sql($sql, [], IGNORE_MISSING);

        foreach ($records as $record) {
            $returnarray[$record->id] = $record->name;
        }

        return $returnarray;
    }
}
