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
 * @package    coursework
 * @author Makouski Denis (khornau@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  Makouski Denis
 */
/**
 * Upgrade the mod_coursework.
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_coursework_upgrade($oldversion)
{
    global $CFG, $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2019012300)
    {
        // Define table coursework_themes to be created.
        $table = new xmldb_table('coursework_themes');
        // Adding fields to table coursework_themes.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('coursework', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        // Adding keys to table coursework_themes.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('coursework', XMLDB_KEY_FOREIGN, array('coursework'), 'coursework', array('id'));
        $table->add_key('course', XMLDB_KEY_FOREIGN, array('course'), 'course', array('id'));
        // Conditionally launch create table for coursework_themes.
        if(!$dbman->table_exists($table))
        {
            $dbman->create_table($table);
        }

        // Adding new fields to coursework_students table
        $table = new xmldb_table('coursework_students');
        $themeField = new xmldb_field('theme', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'course');
        if(!$dbman->field_exists($table, $themeField))
        {
            $dbman->add_field($table, $themeField);
        }
        $ownThemeField = new xmldb_field('owntheme', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'theme');
        if(!$dbman->field_exists($table, $ownThemeField))
        {
            $dbman->add_field($table, $ownThemeField);
        }

        // Coursework savepoint reached.
        upgrade_plugin_savepoint(true, 2019012300, 'mod', 'coursework');
    }

    if($oldversion < 2019081700)
    {
        // Delete coursework_groups table
        $table = new xmldb_table('coursework_groups');
        if($dbman->table_exists($table))
        {
            $dbman->drop_table($table);
        }

        // Rename coursework_tutors table to coursework_teachers
        $table = new xmldb_table('coursework_tutors');
        if($dbman->table_exists($table))
        {
            $dbman->rename_table($table, 'coursework_teachers');
        }

        // Rename field tutor to teacher in coursework_teachers table
        $table = new xmldb_table('coursework_teachers');
        $field = new xmldb_field('tutor');
        if($dbman->field_exists($table, $field))
        {
            $field->set_attributes(XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'tutor');
            $dbman->rename_field($table, $field, 'teacher');
        }

        // Rename field tutor to teacher in coursework_students table
        $table = new xmldb_table('coursework_students');
        $field = new xmldb_field('tutor');
        if($dbman->field_exists($table, $field))
        {
            $field->set_attributes(XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'tutor');
            $dbman->rename_field($table, $field, 'teacher');
        }

        // Delete old foreign key and create new in coursework_teachers table
        $table = new xmldb_table('coursework_teachers');
        $key = new xmldb_key('tutor');
        
        $dbman->drop_key($table, $key);

        $key = new xmldb_key('teacher');
        $key->set_attributes(XMLDB_KEY_FOREIGN, array('teacher'), 'user', array('id'));

        // Delete old foreign key and create new in coursework_students table
        $table = new xmldb_table('coursework_students');
        $key = new xmldb_key('tutor');
        
        $dbman->drop_key($table, $key);

        $key = new xmldb_key('teacher');
        $key->set_attributes(XMLDB_KEY_FOREIGN, array('teacher'), 'user', array('id'));
    }

    if($oldversion < 2019082400)
    {
        // Create coursework_theme_collections table
        $table = new xmldb_table('coursework_theme_collections');
        // Adding fields to table coursework_theme_collections.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, 'small', null, null, null, null);
        // Adding keys to table coursework_theme_collections.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('course', XMLDB_KEY_FOREIGN, array('course'), 'course', array('id'));
        // Conditionally launch create table for coursework_theme_collections.
        if(!$dbman->table_exists($table))
        {
            $dbman->create_table($table);
        }

        // Get all themes from database
        $themes = $DB->get_records('coursework_themes', array(), 'course, coursework');
        // Update coursework_themes table
        $table = new xmldb_table('coursework_themes');
        // Delete old table fields and keys from table
        $key = new xmldb_key('coursework');
        $dbman->drop_key($table, $key);
        $key = new xmldb_key('course');
        $dbman->drop_key($table, $key);
        // Remove indexes to change fields.
        $index = new xmldb_index('mdl_courthem_cou_ix', XMLDB_INDEX_NOTUNIQUE, array('coursework'));
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }
        $index = new xmldb_index('mdl_courthem_cou2_ix', XMLDB_INDEX_NOTUNIQUE, array('course'));
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }
        $field = new xmldb_field('coursework');
        $dbman->drop_field($table, $field);
        $field = new xmldb_field('course');
        $dbman->drop_field($table, $field);
        // Add new field and key to table
        $field = new xmldb_field('collection', XMLDB_TYPE_INTEGER, '10', null, null, null, null, null);
        if(!$dbman->field_exists($table, $field))
        {
            $dbman->add_field($table, $field);
        }
        $key = new xmldb_key('collection', XMLDB_KEY_FOREIGN, array('collection'), 'coursework_theme_collections', array('id'));
        $dbman->add_key($table, $key);
        // Add old themes to collections
        $previousCode = '';
        $newCollectionId = 0;
        foreach($themes as $theme)
        {
            $themeCode = $theme->coursework.'_'.$theme->course;

            // Add new collection
            if($themeCode != $previousCode)
            {
                $collection = new stdClass;
                $collection->course = $theme->course;
                $collection->name = $DB->get_field('coursework', 'name', array('id'=>$theme->coursework));

                $newCollectionId = $DB->insert_record('coursework_theme_collections', $collection, true);

                $previousCode = $themeCode;
            }

            // Update theme
            unset($theme->coursework);
            unset($theme->course);
            $theme->collection = $newCollectionId;
            $DB->update_record('coursework_themes', $theme);
        }

        // Create coursework_used_collections table
        $table = new xmldb_table('coursework_used_collections');
        // Adding fields to table coursework_used_collections.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('coursework', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('collection', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        // Adding keys to table coursework_used_collections.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('coursework', XMLDB_KEY_FOREIGN, array('coursework'), 'coursework', array('id'));
        $table->add_key('collection', XMLDB_KEY_FOREIGN, array('collection'), 'coursework_theme_collections', array('id'));
        // Conditionally launch create table for coursework_used_collections.
        if(!$dbman->table_exists($table))
        {
            $dbman->create_table($table);
        }
    }

    if($oldversion < 2019082500)
    {
        // Create coursework_tasks table
        $table = new xmldb_table('coursework_tasks');
        // Adding fields to table coursework_tasks.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, 'small', null, null, null, null);
        $table->add_field('template', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, 0, null);
        // Adding keys to table coursework_tasks.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        // Conditionally launch create table for coursework_tasks.
        if(!$dbman->table_exists($table))
        {
            $dbman->create_table($table);
        }
    }

    return true;
}
