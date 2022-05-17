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
 * @package moodlecore
 * @subpackage backup-moodle2
 * @copyright 2010 onwards YOUR_NAME_GOES_HERE {@link YOUR_URL_GOES_HERE}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This activity has not particular settings but the inherited from the generic
// backup_activity_coursework so here there isn't any class definition, like the ones
// existing in /backup/moodle2/backup_settingslib.php (activities section)

/**
 * Define the complete coursework structure for backup, with file and id annotations
 */

class backup_coursework_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() 
    {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        $coursework = new backup_nested_element('coursework', array('id'), array(
            'name', 'intro', 'introformat', 'timemodified', 
            'usetask', 'autotaskissuance', 'maxfilesize', 
            'maxfilesnumber', 'defaulttask', 'deadline'
        ));

        $defaultTasks = new backup_nested_element('defaultTasks');
        $defaultTask = new backup_nested_element('defaultTask', array('id'), array(
            'name', 'description', 'template'));

        $defaultTasksSections = new backup_nested_element('defaultTasksSections');
        $defaultTaskSection = new backup_nested_element('defaultTaskSection', array('id'), array(
            'name', 'description', 'listposition', 'deadline'));

        $collectionsUses = new backup_nested_element('collectionsUses');
        $collectionUse = new backup_nested_element('collectionUse', array('id'), array(
            'collection', 'samethemescount'));

        $suggestedCollections = new backup_nested_element('suggestedCollections');
        $suggestedCollection = new backup_nested_element('suggestedCollection', array('id'), array(
            'course', 'name', 'description'));

        $suggestedThemes = new backup_nested_element('suggestedThemes');
        $suggestedTheme = new backup_nested_element('suggestedTheme', array('id'), array('content'));

        $teachers = new backup_nested_element('teachers');
        $teacher = new backup_nested_element('teacher', array('id'), array(
            'teacher', 'course', 'quota'));

        $students = new backup_nested_element('students');
        $student = new backup_nested_element('student', array('id'), array(
            'student', 'teacher', 'course',
            'theme', 'owntheme', 'task',
            'grade'
        ));

        $studentsThemes = new backup_nested_element('studentsThemes');
        $studentTheme = new backup_nested_element('studentTheme', array('id'), array(
            'content', 'collection'));

        $studentsTasks = new backup_nested_element('studentsTasks');
        $studentTask = new backup_nested_element('studentTask', array('id'), array(
            'name', 'description', 'template'));

        $studentsTasksSections = new backup_nested_element('studentsTasksSections');
        $studentTaskSection = new backup_nested_element('studentTaskSection', array('id'), array(
            'name', 'description', 'listposition', 'deadline'));

        $statuses = new backup_nested_element('statuses');
        $status = new backup_nested_element('status', array('id'), array(
            'student', 'type', 'instance',
            'status', 'changetime' 
        ));

        $chats = new backup_nested_element('chats');
        $chat = new backup_nested_element('chat', array('id'), array(
            'userfrom', 'userto', 'content',
            'sendtime', 'readed', 'type'
        ));

        // Build the tree
        $coursework->add_child($defaultTasks);

        $defaultTasks->add_child($defaultTask);
        $defaultTask->add_child($defaultTasksSections);
        $defaultTasksSections->add_child($defaultTaskSection);
        $coursework->add_child($collectionsUses);
        $collectionsUses->add_child($collectionUse);
        $collectionUse->add_child($suggestedCollections);
        $suggestedCollections->add_child($suggestedCollection);
        $suggestedCollection->add_child($suggestedThemes);
        $suggestedThemes->add_child($suggestedTheme);

        $coursework->add_child($teachers);
        $teachers->add_child($teacher);

        $coursework->add_child($students);
        $students->add_child($student);
        $student->add_child($studentsTasks);
        $studentsTasks->add_child($studentTask);
        $studentTask->add_child($studentsTasksSections);
        $studentsTasksSections->add_child($studentTaskSection);
        $student->add_child($studentsThemes);
        $studentsThemes->add_child($studentTheme);

        $coursework->add_child($statuses);
        $statuses->add_child($status);

        $coursework->add_child($chats);
        $chats->add_child($chat);

        // Define sources
        $coursework->set_source_table('coursework', array('id' => backup::VAR_ACTIVITYID));

        $defaultTask->set_source_table('coursework_tasks', array('id' => '../../defaulttask'));
        $defaultTaskSection->set_source_table('coursework_tasks_sections', array('task' => '../../id'));

        $collectionUse->set_source_table('coursework_themes_collections_use', array('coursework' => '../../id'));
        $suggestedCollection->set_source_table('coursework_themes_collections', array('id' => '../../collection'));
        $suggestedTheme->set_source_table('coursework_themes', array('collection' => '../../id'));

        // All the rest of elements only happen if we are including user info
        if($userinfo) 
        {
            $teacher->set_source_table('coursework_teachers', array('coursework' => '../../id'));

            $student->set_source_table('coursework_students', array('coursework' => '../../id'));
            $studentTask->set_source_table('coursework_tasks', array('id' => '../../task'));
            $studentTaskSection->set_source_table('coursework_tasks_sections', array('task' => '../../id'));
            $studentTheme->set_source_table('coursework_themes', array('id' => '../../theme'));

            $status->set_source_table('coursework_students_statuses', array('coursework' => '../../id'));
            $chat->set_source_table('coursework_chat', array('coursework' => '../../id'));
        }

        // Define id annotations
        $suggestedCollection->annotate_ids('course', 'course');

        $teacher->annotate_ids('user', 'teacher');
        $teacher->annotate_ids('course', 'course');

        $student->annotate_ids('user', 'student');
        $student->annotate_ids('user', 'teacher');
        $student->annotate_ids('course', 'course');

        $status->annotate_ids('user', 'student');

        $chat->annotate_ids('user', 'userfrom');
        $chat->annotate_ids('user', 'userto');

        // Define file annotations
        $coursework->annotate_files('mod_coursework', 'intro', null);
        $student->annotate_files('mod_coursework', 'student', 'student');
        $student->annotate_files('mod_coursework', 'teacher', 'student');

        // Return the root element (coursework), wrapped into standard activity structure
        return $this->prepare_activity_structure($coursework);

    }




}
