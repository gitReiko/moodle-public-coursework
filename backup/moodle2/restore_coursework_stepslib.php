<?php 

require_once $CFG->dirroot.'/mod/coursework/lib/enums.php';

use Coursework\Lib\Enums;

/**
 * Structure step to restore one coursework activity
 */
class restore_coursework_activity_structure_step extends restore_activity_structure_step 
{
    private $defaultTask;
    private $collectionMatching = array();

    protected function define_structure() 
    {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('coursework', '/activity/coursework');
        $paths[] = new restore_path_element('defaultTask', '/activity/coursework/defaultTasks/defaultTask');
        $paths[] = new restore_path_element('defaultTaskSection', '/activity/coursework/defaultTasks/defaultTask/defaultTasksSections/defaultTaskSection');
        $paths[] = new restore_path_element('collectionUse', '/activity/coursework/collectionsUses/collectionUse');
        $paths[] = new restore_path_element('suggestedCollection', '/activity/coursework/collectionsUses/collectionUse/suggestedCollections/suggestedCollection');
        $paths[] = new restore_path_element('suggestedTheme', '/activity/coursework/collectionsUses/collectionUse/suggestedCollections/suggestedCollection/suggestedThemes/suggestedTheme');
        
        if($userinfo) 
        {
            $paths[] = new restore_path_element('teacher', '/activity/coursework/teachers/teacher');
            $paths[] = new restore_path_element('student', '/activity/coursework/students/student');
            $paths[] = new restore_path_element('studentTheme', '/activity/coursework/students/student/studentsThemes/studentTheme');
            $paths[] = new restore_path_element('studentTask', '/activity/coursework/students/student/studentsTasks/studentTask');
            $paths[] = new restore_path_element('studentTaskSection', '/activity/coursework/students/student/studentsTasks/studentTask/studentsTasksSections/studentTaskSection');
            $paths[] = new restore_path_element('status', '/activity/coursework/statuses/status');
            $paths[] = new restore_path_element('chat', '/activity/coursework/chats/chat');
        }

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_coursework($data) 
    {
        global $DB;

        $data = (object)$data;

        $data->course = $this->get_courseid();

        $newId = $DB->insert_record('coursework', $data);

        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newId);
    }

    protected function process_defaultTask($data) 
    {
        global $DB;

        $data = (object)$data;
        $oldId = $data->id;
        $data->name .= $this->get_backup_signature();

        $newId = $DB->insert_record('coursework_tasks', $data);

        $this->defaultTask = new \stdClass;
        $this->defaultTask->oldId = $oldId;
        $this->defaultTask->newId = $newId;

        $this->update_defaulttask_field_in_coursework_table($newId);
        
        $this->set_mapping('defaultTask', $oldId, $newId);
    }

    private function get_backup_signature()
    {
        return ' backup '.date('m/d/y - H:i');
    }

    private function update_defaulttask_field_in_coursework_table(int $newDefaultTaskId)
    {
        global $DB;

        $where = array('id' => $this->get_new_parentid('coursework'));
        $coursework = $DB->get_record('coursework', $where);
        
        $coursework->defaulttask = $newDefaultTaskId;
        
        $DB->update_record('coursework', $coursework);
    }

    protected function process_defaultTaskSection($data) 
    {
        global $DB;

        $data = (object)$data;

        $data->task = $this->get_new_parentid('defaultTask');

        $DB->insert_record('coursework_tasks_sections', $data);
    }

    protected function process_collectionUse($data) 
    {
        global $DB;

        $data = (object)$data;
        $oldId = $data->id;

        $data->coursework = $this->get_new_parentid('coursework');

        $newId = $DB->insert_record('coursework_themes_collections_use', $data);
        
        $this->set_mapping('collectionUse', $oldId, $newId);
    }

    protected function process_suggestedCollection($data) 
    {
        global $DB;

        $data = (object)$data;
        $oldId = $data->id;

        $data->name .= $this->get_backup_signature();

        $newId = $DB->insert_record('coursework_themes_collections', $data);

        $this->add_collection_matching($oldId, $newId);

        $this->update_collection_field_in_collections_use_table($newId);
        
        $this->set_mapping('suggestedCollection', $oldId, $newId);
    }

    private function add_collection_matching(int $oldId, int $newId)
    {
        $collection = new \stdClass;
        $collection->oldId = $oldId;
        $collection->newId = $newId;

        $this->collectionMatching[] = $collection;
    }

    private function update_collection_field_in_collections_use_table(int $newCollectionId)
    {
        global $DB;

        $where = array('id' => $this->get_new_parentid('collectionUse'));
        $collectionUse = $DB->get_record('coursework_themes_collections_use', $where);
        
        $collectionUse->collection = $newCollectionId;
        
        $DB->update_record('coursework_themes_collections_use', $collectionUse);
    }

    protected function process_suggestedTheme($data) 
    {
        global $DB;

        $data = (object)$data;

        $data->collection = $this->get_new_parentid('suggestedCollection');

        $DB->insert_record('coursework_themes', $data);
    }

    protected function process_teacher($data) 
    {
        global $DB;

        $data = (object)$data;

        $data->coursework = $this->get_new_parentid('coursework');
        $data->teacher = $this->get_mappingid('user', $data->teacher);

        $DB->insert_record('coursework_teachers', $data);
    }

    protected function process_student($data) 
    {
        global $DB;

        $data = (object)$data;
        $oldId = $data->id;

        $data->coursework = $this->get_new_parentid('coursework');
        $data->student = $this->get_mappingid('user', $data->student);
        $data->teacher = $this->get_mappingid('user', $data->teacher);
        
        if($data->task == $this->defaultTask->oldId)
        {
            $data->task = $this->defaultTask->newId;
        }

        $newId = $DB->insert_record('coursework_students', $data);

        $this->set_mapping('student', $oldId, $newId, true);
    }

    protected function process_studentTheme($data) 
    {
        $data = (object)$data;
        $oldId = $data->id;

        $data->collection = $this->get_new_collection_id($data->collection);

        if($this->is_theme_not_exists($data))
        {
            $newId = $this->add_theme_to_database($data);
        }
        else 
        {
            $newId = $this->get_theme_id($data);
        }

        $this->update_students_theme_field($oldId, $newId);
    }

    private function get_new_collection_id(int $oldId)
    {
        foreach($this->collectionMatching as $matching)
        {
            if($matching->oldId == $oldId)
            {
                return $matching->newId;
            }
        }
    }

    private function is_theme_not_exists($data) : bool 
    {
        global $DB;
        $sql = "SELECT id 
                FROM {coursework_themes}
                WHERE `collection` = ".$data->collection."
                AND content LIKE '".$data->content."'";
        return !$DB->record_exists_sql($sql);
    }

    private function add_theme_to_database($data) 
    {
        global $DB;
        return $DB->insert_record('coursework_themes', $data);
    }

    private function get_theme_id($data)
    {
        global $DB;
        $sql = "SELECT id 
                FROM {coursework_themes}
                WHERE `collection` = ".$data->collection."
                AND content LIKE '".$data->content."'";
        return $DB->get_field_sql($sql);
    }

    private function update_students_theme_field($oldThemeId, $newThemeId)
    {
        global $DB;

        $where = array(
            'coursework' => $this->get_new_parentid('coursework'),
            'theme' => $oldThemeId
        );
        $students = $DB->get_records('coursework_students', $where);

        foreach($students as $student)
        {
            $student->theme = $newThemeId;

            $DB->update_record('coursework_students', $student);
        }
    }

    protected function process_studentTask($data) 
    {
        global $DB;
        $data = (object)$data;
        $oldId = $data->id;
        
        $data->name .= $this->get_backup_signature();

        $newId = $DB->insert_record('coursework_tasks', $data);

        $this->set_mapping('studentTask', $oldId, $newId);

        $this->update_task_field_in_students_table($oldId, $newId);
    }

    private function update_task_field_in_students_table(int $oldTaskId, int $newTaskId)
    {
        global $DB;

        $where = array(
            'coursework' => $this->get_new_parentid('coursework'),
            'task' => $oldTaskId
        );
        $students = $DB->get_records('coursework_students', $where);

        foreach($students as $student)
        {
            $student->task = $newTaskId;

            $DB->update_record('coursework_students', $student);
        }
    }

    protected function process_studentTaskSection($data) 
    {
        global $DB;

        $data = (object)$data;

        $data->task = $this->get_new_parentid('studentTask');

        $DB->insert_record('coursework_tasks_sections', $data);
    }

    protected function process_status($data) 
    {
        global $DB;

        $data = (object)$data;
        $data->coursework = $this->get_new_parentid('coursework');
        $data->student = $this->get_mappingid('user', $data->student);

        if($data->type == Enums::COURSEWORK)
        {
            $data->instance = $this->get_new_parentid('coursework');
        }

        $DB->insert_record('coursework_students_statuses', $data);
    }

    protected function process_chat($data) 
    {
        global $DB;

        $data = (object)$data;
        $data->coursework = $this->get_new_parentid('coursework');
        $data->userfrom = $this->get_mappingid('user', $data->userfrom);
        $data->userto = $this->get_mappingid('user', $data->userto);

        $DB->insert_record('coursework_chat', $data);
    }

    protected function after_execute() 
    {
        // Add coursework related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_coursework', 'intro', null);
        $this->add_related_files('mod_coursework', 'student', 'student');
        $this->add_related_files('mod_coursework', 'teacher', 'student');
    }

}
