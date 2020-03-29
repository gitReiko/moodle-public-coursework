<?php 

use coursework_lib as lib;

class CollectionsDBEventsHandler 
{
    private $course;
    private $cm;

    private $collection;

    function __construct(stdClass $course, stdClass $cm) 
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->collection = $this->get_collection();
    }

    public function execute() : void 
    {
        $event = optional_param(ConfigurationManager::DATABASE_EVENT, null, PARAM_TEXT);

        switch($event)
        {
            case CollectionsManagement::ADD_COLLECTION: 
                $this->add_collection();
                break;
            case CollectionsManagement::EDIT_COLLECTION: 
                $this->update_collection();
                break;
        }
    }

    private function get_collection() : stdClass 
    {
        $collection = new stdClass;

        $id = $this->get_collection_id();
        if(!empty($id)) $collection->id = $id;

        $collection->course = $this->get_collection_course();
        $collection->name = $this->get_collection_name();

        $description = $this->get_collection_description();
        if(!empty($description)) $collection->description = $description;

        return $collection;
    }

    private function get_collection_id() 
    {
        return optional_param(COLLECTION.ID, null, PARAM_INT);
    }

    private function get_collection_course() : int 
    {
        $course = optional_param(COURSE, null, PARAM_INT);
        if(empty($course)) throw new Exception('Missing course id.');
        return $course;
    }

    private function get_collection_name() : string 
    {
        $name = optional_param(NAME, null, PARAM_TEXT);
        if(empty($name)) throw new Exception('Missing themes collection name.');
        return $name;
    }

    private function get_collection_description() 
    {
        return optional_param(DESCRIPTION, null, PARAM_TEXT);
    }

    private function add_collection() : void 
    {
        global $DB;
        $DB->insert_record('coursework_theme_collections', $this->collection, false);
    }

    private function update_collection() : void 
    {
        if(empty($this->collection->id)) throw new Exception('Missing collection id.');

        global $DB;
        $DB->update_record('coursework_theme_collections', $this->collection);
    }



}

