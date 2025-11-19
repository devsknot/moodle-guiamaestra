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

require('../../config.php');

require_login();

$context = context_system::instance();
require_capability('local/wb_news:manage', $context);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/wb_news/instances.php'));
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('instancesheading', 'local_wb_news'));
$PAGE->set_heading(get_string('instancesheading', 'local_wb_news'));

$sql = "SELECT wni.id,
               wni.name,
               wni.template,
               wni.columns,
               wni.contextids,
               wni.timecreated,
               wni.timemodified,
               COUNT(wn.id) AS articlecount,
               MAX(wn.timemodified) AS lastarticle
          FROM {local_wb_news_instance} wni
     LEFT JOIN {local_wb_news} wn ON wn.instanceid = wni.id AND wn.instanceid > 0
      GROUP BY wni.id, wni.name, wni.template, wni.columns, wni.contextids, wni.timecreated, wni.timemodified
      ORDER BY wni.id ASC";

$records = $DB->get_records_sql($sql);

$rows = [];

foreach ($records as $record) {
    $instancecontexts = [];
    if (!empty($record->contextids)) {
        $contextids = explode(',', $record->contextids);
        foreach ($contextids as $contextid) {
            $contextid = (int)trim($contextid);
            if (empty($contextid)) {
                continue;
            }
            try {
                $ctx = context::instance_by_id($contextid, IGNORE_MISSING);
                if ($ctx) {
                    $instancecontexts[] = $ctx->get_context_name(false, true);
                }
            } catch (Exception $e) {
                // Ignore missing contexts.
            }
        }
    }

    if (empty($instancecontexts)) {
        $instancecontexts[] = get_string('system', 'local_wb_news');
    }

    $articlecount = (int)$record->articlecount;
    $lastarticle = $record->lastarticle ? (int)$record->lastarticle : 0;
    $lastchange = $lastarticle ?: (int)$record->timemodified ?: (int)$record->timecreated ?: 0;

    $lastchangeformatted = $lastchange
        ? userdate($lastchange, get_string('strftimedatetimeshort', 'core_langconfig'))
        : get_string('never');

    $manageurl = new moodle_url('/local/wb_news/index.php', ['id' => $record->id]);
    $managebutton = html_writer::link(
        $manageurl,
        get_string('instancesmanage', 'local_wb_news'),
        ['class' => 'btn btn-secondary btn-sm']
    );

    $rows[] = new html_table_row([
        format_string($record->name ?? ''),
        s($record->template ?? ''),
        (int)($record->columns ?? 0),
        implode('<br>', array_map('s', $instancecontexts)),
        $articlecount,
        $lastchangeformatted,
        $managebutton,
    ]);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('instancesheading', 'local_wb_news'));

echo html_writer::tag('p', get_string('instancesdescription', 'local_wb_news'), ['class' => 'mb-4']);

if (empty($rows)) {
    echo $OUTPUT->notification(get_string('instancesnone', 'local_wb_news'), 'info');
} else {
    $table = new html_table();
    $table->head = [
        get_string('instancesname', 'local_wb_news'),
        get_string('instancestemplate', 'local_wb_news'),
        get_string('instancescolumns', 'local_wb_news'),
        get_string('instancescontexts', 'local_wb_news'),
        get_string('instancesarticles', 'local_wb_news'),
        get_string('instanceslastmodified', 'local_wb_news'),
        get_string('instancesactions', 'local_wb_news'),
    ];
    $table->data = $rows;
    $table->attributes['class'] = 'generaltable wb-news-instances-table';

    echo html_writer::div(html_writer::table($table), 'table-responsive');
}

echo $OUTPUT->footer();
