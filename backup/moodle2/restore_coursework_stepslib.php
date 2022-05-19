<?php 

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
        }

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_coursework($data) 
    {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        // insert the choice record
        $newitemid = $DB->insert_record('coursework', $data);

        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }

    protected function process_defaultTask($data) 
    {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->name .= ' backup '.date('m/d/y - H:i');

        $newitemid = $DB->insert_record('coursework_tasks', $data);

        $this->defaultTask = new \stdClass;
        $this->defaultTask->oldId = $oldid;
        $this->defaultTask->newId = $newitemid;

        $this->update_coursework_table_defaulttask_field($newitemid);
        
        $this->set_mapping('defaultTask', $oldid, $newitemid);
    }

    private function update_coursework_table_defaulttask_field(int $newDefaultTaskId)
    {
        global $DB;

        $where = array('id' => $this->get_new_parentid('coursework'));
        $coursework = $DB->get_record('coursework', $where);
        
        $coursework->defaulttask = $newDefaultTaskId;
        
        return $DB->update_record('coursework', $coursework);
    }

    protected function process_defaultTaskSection($data) 
    {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->task = $this->get_new_parentid('defaultTask');

        $newitemid = $DB->insert_record('coursework_tasks_sections', $data);
    }

    protected function process_collectionUse($data) 
    {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->coursework = $this->get_new_parentid('coursework');

        $newitemid = $DB->insert_record('coursework_themes_collections_use', $data);
        
        $this->set_mapping('collectionUse', $oldid, $newitemid);
    }

    protected function process_suggestedCollection($data) 
    {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->name .= ' backup '.date('m/d/y - H:i');

        $newitemid = $DB->insert_record('coursework_themes_collections', $data);

        $this->add_collection_matching($oldid, $newitemid);

        $this->update_collection_use_table_collection_field($newitemid);
        
        $this->set_mapping('suggestedCollection', $oldid, $newitemid);
    }

    private function add_collection_matching(int $oldId, int $newId)
    {
        $collection = new \stdClass;
        $collection->oldId = $oldId;
        $collection->newId = $newId;

        $this->collectionMatching[] = $collection;
    }

    private function update_collection_use_table_collection_field(int $newCollectionId)
    {
        global $DB;

        $where = array('id' => $this->get_new_parentid('collectionUse'));
        $collectionUse = $DB->get_record('coursework_themes_collections_use', $where);
        
        $collectionUse->collection = $newCollectionId;
        
        return $DB->update_record('coursework_themes_collections_use', $collectionUse);
    }

    protected function process_suggestedTheme($data) 
    {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->collection = $this->get_new_parentid('suggestedCollection');

        $newitemid = $DB->insert_record('coursework_themes', $data);
    }

    protected function process_teacher($data) 
    {
        global $DB;

        $data = (object)$data;

        $data->coursework = $this->get_new_parentid('coursework');
        $data->teacher = $this->get_mappingid('user', $data->teacher);

        $newitemid = $DB->insert_record('coursework_teachers', $data);
    }

    protected function process_student($data) 
    {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->coursework = $this->get_new_parentid('coursework');
        $data->student = $this->get_mappingid('user', $data->student);
        $data->teacher = $this->get_mappingid('user', $data->teacher);
        
        if($data->task == $this->defaultTask->oldId)
        {
            $data->task = $this->defaultTask->newId;
        }

        $newitemid = $DB->insert_record('coursework_students', $data);

        $this->set_mapping('student', $oldid, $newitemid);
    }

    protected function process_studentTheme($data) 
    {
        $data = (object)$data;
        $oldid = $data->id;

        $data->collection = $this->get_new_collection_id($data->collection);

        if($this->is_theme_not_exists($data))
        {
            $newitemid = $this->add_theme_to_database($data);
        }
        else 
        {
            $newitemid = $this->get_theme_id($data);
        }

        $this->update_students_theme_field($oldid, $newitemid);
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
        $oldid = $data->id;
        
        $data->name .= ' backup '.date('m/d/y - H:i');

        $newitemid = $DB->insert_record('coursework_tasks', $data);

        $this->set_mapping('studentTask', $oldid, $newitemid);

        $this->update_task_field_in_student_table($oldid, $newitemid);
    }

    private function update_task_field_in_student_table(int $oldTaskId, int $newTaskId)
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

    protected function after_execute() 
    {
        // Add coursework related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_coursework', 'intro', null);
    }

}
