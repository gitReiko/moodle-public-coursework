<?php 

/**
 * Structure step to restore one coursework activity
 */
class restore_coursework_activity_structure_step extends restore_activity_structure_step 
{

    protected function define_structure() 
    {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('coursework', '/activity/coursework');
        $paths[] = new restore_path_element('defaultTask', '/activity/coursework/defaultTasks/defaultTask');
        $paths[] = new restore_path_element('defaultTaskSection', '/activity/coursework/defaultTasks/defaultTask/defaultTasksSections/defaultTaskSection');
        $paths[] = new restore_path_element('collectionUse', '/activity/coursework/collectionsUses/collectionUse');
        $paths[] = new restore_path_element('suggestedCollection', '/activity/coursework/collectionsUses/collectionUse/suggestedCollections/suggestedCollection');

        

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

        $this->update_collection_use_table_collection_field($newitemid);
        
        $this->set_mapping('suggestedCollection', $oldid, $newitemid);
    }

    private function update_collection_use_table_collection_field(int $newCollectionId)
    {
        global $DB;

        $where = array('id' => $this->get_new_parentid('collectionUse'));
        $collectionUse = $DB->get_record('coursework_themes_collections_use', $where);
        
        $collectionUse->collection = $newCollectionId;
        
        return $DB->update_record('coursework_themes_collections_use', $collectionUse);
    }

    protected function after_execute() 
    {
        // Add coursework related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_coursework', 'intro', null);
    }

}
