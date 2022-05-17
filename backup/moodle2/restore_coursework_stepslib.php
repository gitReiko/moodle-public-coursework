<?php 

/**
 * Structure step to restore one coursework activity
 */
class restore_coursework_activity_structure_step extends restore_activity_structure_step 
{

    private $defaultTaskId;

    protected function define_structure() 
    {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('coursework', '/activity/coursework');
        $paths[] = new restore_path_element('defaultTask', '/activity/coursework/defaultTasks/defaultTask');
        $paths[] = new restore_path_element('defaultTaskSection', '/activity/coursework/defaultTasks/defaultTask/defaultTasksSections/defaultTaskSection');
        $paths[] = new restore_path_element('collectionUse', '/activity/coursework/collectionsUses/collectionUse');


        

        /*
        $paths[] = new restore_path_element('choice_option', '/activity/choice/options/option');
        if ($userinfo) {
            $paths[] = new restore_path_element('choice_answer', '/activity/choice/answers/answer');
        }
        */

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

        $this->defaultTaskId = $newitemid;
        $this->update_coursework_table_defaulttask_field($newitemid);
        
        $this->set_mapping('coursework_tasks', $oldid, $newitemid);
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

        $data->task = $this->defaultTaskId;

        $newitemid = $DB->insert_record('coursework_tasks_sections', $data);
        $this->set_mapping('coursework_tasks_sections', $oldid, $newitemid);
    }

    /*
    protected function process_choice_option($data) 
    {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->choiceid = $this->get_new_parentid('choice');

        $newitemid = $DB->insert_record('choice_options', $data);
        $this->set_mapping('choice_option', $oldid, $newitemid);
    }

    protected function process_choice_answer($data) 
    {
        global $DB;

        $data = (object)$data;

        $data->choiceid = $this->get_new_parentid('choice');
        $data->optionid = $this->get_mappingid('choice_option', $data->optionid);
        $data->userid = $this->get_mappingid('user', $data->userid);

        $newitemid = $DB->insert_record('choice_answers', $data);
        // No need to save this mapping as far as nothing depend on it
        // (child paths, file areas nor links decoder)
    }
    */

    protected function after_execute() 
    {
        // Add coursework related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_coursework', 'intro', null);
    }

}
