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
 * Upgrade script for the quiz module.
 *
 * @package    local_wb_news
 * @copyright  2024 Wunderbyte GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * WB News Upgrade
 * @param string $oldversion the version we are upgrading from.
 */
function xmldb_local_wb_news_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2024061501) {
        // Define field lightmode to be added to local_wb_news.
        $table = new xmldb_table('local_wb_news');
        $field = new xmldb_field('lightmode', XMLDB_TYPE_INTEGER, '2', null, null, null, null, 'btntext');

        // Conditionally launch add field lightmode.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field cssclasses to be added to local_wb_news.
        $table = new xmldb_table('local_wb_news');
        $field = new xmldb_field('cssclasses', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'lightmode');

        // Conditionally launch add field cssclasses.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Wb_news savepoint reached.
        upgrade_plugin_savepoint(true, 2024061501, 'local', 'wb_news');
    }

    if ($oldversion < 2024061503) {
        // Define field imagemode to be added to local_wb_news.
        $table = new xmldb_table('local_wb_news');
        $field = new xmldb_field('imagemode', XMLDB_TYPE_INTEGER, '2', null, null, null, null, 'active');

        // Conditionally launch add field lightmode.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Wb_news savepoint reached.
        upgrade_plugin_savepoint(true, 2024061503, 'local', 'wb_news');
    }

    if ($oldversion < 2024061600) {
        // Define field bgcolor to be added to local_wb_news.
        $table = new xmldb_table('local_wb_news');
        $field = new xmldb_field('bgcolor', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'icon');

        // Conditionally launch add field lightmode.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Wb_news savepoint reached.
        upgrade_plugin_savepoint(true, 2024061600, 'local', 'wb_news');
    }

    if ($oldversion < 2024070139) {
        // Define field contextids to be added to local_wb_news_instance.
        $table = new xmldb_table('local_wb_news_instance');
        $field = new xmldb_field('contextids', XMLDB_TYPE_CHAR, '1333', null, null, null, null, 'userid');

        // Conditionally launch add field contextids.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Wb_news savepoint reached.
        upgrade_plugin_savepoint(true, 2024070139, 'local', 'wb_news');
    }

    if ($oldversion < 2024071201) {
        // Define field columns to be added to local_wb_news_instance.
        $table = new xmldb_table('local_wb_news_instance');
        $field = new xmldb_field('columns', XMLDB_TYPE_INTEGER, '2', null, null, null, '4', 'userid');

        // Conditionally launch add field columns.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Wb_news savepoint reached.
        upgrade_plugin_savepoint(true, 2024071201, 'local', 'wb_news');
    }

    if ($oldversion < 2024071205) {
        // Define field columns to be added to local_wb_news_instance.
        $table = new xmldb_table('local_wb_news');
        $field = new xmldb_field('json', XMLDB_TYPE_CHAR, '1333', null, null, null, null, 'cssclasses');

        // Conditionally launch add field columns.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Wb_news savepoint reached.
        upgrade_plugin_savepoint(true, 2024071205, 'local', 'wb_news');
    }

    if ($oldversion < 2024082600) {
        // Define field bgimagetext to be added to local_wb_news.
        $table = new xmldb_table('local_wb_news');
        $field = new xmldb_field('bgimagetext', XMLDB_TYPE_CHAR, '1333', null, null, null, null, 'bgimage');

        // Conditionally launch add field bgimagetext.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('icontext', XMLDB_TYPE_CHAR, '1333', null, null, null, null, 'icon');

        // Conditionally launch add field icontext.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Wb_news savepoint reached.
        upgrade_plugin_savepoint(true, 2024082600, 'local', 'wb_news');
    }

    // Automatically generated Moodle v4.0.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v4.1.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2024093002) {
        // Changing precision of field bgimage on table local_wb_news to (1333).
        $table = new xmldb_table('local_wb_news');
        $field = new xmldb_field('bgimage', XMLDB_TYPE_CHAR, '1333', null, null, null, null, 'sortorder');

        // Launch change of precision for field bgimage.
        $dbman->change_field_precision($table, $field);

        // Changing precision of field bgimage on table local_wb_news to (1333).
        $field = new xmldb_field('icon', XMLDB_TYPE_CHAR, '1333', null, null, null, null, 'bgimagetext');

        // Launch change of precision for field bgimage.
        $dbman->change_field_precision($table, $field);

        // Changing precision of field bgimage on table local_wb_news to (1333).
        $field = new xmldb_field('headline', XMLDB_TYPE_CHAR, '1333', null, null, null, null, 'userid');

        // Launch change of precision for field bgimage.
        $dbman->change_field_precision($table, $field);

        // Changing precision of field bgimage on table local_wb_news to (1333).
        $field = new xmldb_field('subheadline', XMLDB_TYPE_CHAR, '1333', null, null, null, null, 'headline');

        // Launch change of precision for field bgimage.
        $dbman->change_field_precision($table, $field);

        // Changing precision of field bgimage on table local_wb_news to (1333).
        $field = new xmldb_field('btnlink', XMLDB_TYPE_CHAR, '1333', null, null, null, null, 'descriptionformat');

        // Launch change of precision for field bgimage.
        $dbman->change_field_precision($table, $field);

        // Changing precision of field bgimage on table local_wb_news to (1333).
        $field = new xmldb_field('btntext', XMLDB_TYPE_CHAR, '1333', null, null, null, null, 'btnlink');

        // Launch change of precision for field bgimage.
        $dbman->change_field_precision($table, $field);

        // Changing precision of field bgimage on table local_wb_news to (1333).
        $field = new xmldb_field('cssclasses', XMLDB_TYPE_CHAR, '1333', null, null, null, null, 'lightmode');

        // Launch change of precision for field bgimage.
        $dbman->change_field_precision($table, $field);

        // Wb_news savepoint reached.
        upgrade_plugin_savepoint(true, 2024093002, 'local', 'wb_news');
    }

    if ($oldversion < 2024101801) {
        // Changing field bgimage on table local_wb_news to handle a large text size.
        $table = new xmldb_table('local_wb_news');
        $field = new xmldb_field('bgimage', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, 'sortorder');

        // Launch change of field type to TEXT for bgimage.
        $dbman->change_field_type($table, $field);

        $field = new xmldb_field('bgimagetext', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, 'bgimage');

        // Launch change of field type to TEXT for bgimagetext.
        $dbman->change_field_type($table, $field);

        $field = new xmldb_field('icon', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, 'bgimagetext');

        // Launch change of field type to TEXT for icon.
        $dbman->change_field_type($table, $field);

        $field = new xmldb_field('icontext', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, 'icon');

        // Launch change of field type to TEXT for icon.
        $dbman->change_field_type($table, $field);

        $field = new xmldb_field('headline', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, 'userid');

        // Launch change of field type to TEXT for headline.
        $dbman->change_field_type($table, $field);

        $field = new xmldb_field('subheadline', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, 'headline');

        // Launch change of field type to TEXT for subheadline.
        $dbman->change_field_type($table, $field);

        $field = new xmldb_field('btnlink', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, 'descriptionformat');

        // Launch change of field type to TEXT for btnlink.
        $dbman->change_field_type($table, $field);

        $field = new xmldb_field('btntext', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, 'btnlink');

        // Launch change of field type to TEXT for btntext.
        $dbman->change_field_type($table, $field);

        $field = new xmldb_field('cssclasses', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, 'lightmode');

        // Launch change of field type to TEXT for cssclasses.
        $dbman->change_field_type($table, $field);

        // Wb_news savepoint reached.
        upgrade_plugin_savepoint(true, 2024101801, 'local', 'wb_news');
    }

    if ($oldversion < 2024122004) {
        // Define field btnlinkattributes to be added to local_wb_news.
        $table = new xmldb_table('local_wb_news');
        $field = new xmldb_field('btnlinkattributes', XMLDB_TYPE_TEXT, null, null, null, null, null, 'btnlink');

        // Conditionally launch add field btnlinkattributes.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Wb_news savepoint reached.
        upgrade_plugin_savepoint(true, 2024122004, 'local', 'wb_news');
    }

    return true;
}
