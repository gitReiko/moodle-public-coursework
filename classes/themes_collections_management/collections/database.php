<?php

namespace Coursework\View\ThemesCollectionsManagement\Collections;

use Coursework\View\ThemesCollectionsManagement\Main;

class Database 
{
    private $course;
    private $cm;

    private $collection;

    function __construct(\stdClass $course, \stdClass $cm) 
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->collection = $this->get_collection();
    }

    public function execute() : void 
    {
        $event = optional_param(Main::DATABASE_EVENT, null, PARAM_TEXT);

        switch($event)
        {
            case Main::ADD_COLLECTION: 
                $this->add_collection();
                break;
            case Main::EDIT_COLLECTION: 
                $this->update_collection();
                break;
        }
    }

    private function get_collection() : \stdClass 
    {
        $collection = new \stdClass;

        $id = $this->get_collection_id();
        if(!empty($id)) $collection->id = $id;

        $collection->course = $this->get_collection_course();
        $collection->name = $this->get_collection_name();
        $collection->description = $this->get_collection_description();

        return $collection;
    }

    private function get_collection_id() 
    {
        return optional_param(Main::COLLECTION_ID, null, PARAM_INT);
    }

    private function get_collection_course() : int 
    {
        $course = optional_param(Main::COURSE, null, PARAM_INT);
        if(empty($course)) throw new \Exception('Missing course id.');
        return $course;
    }

    private function get_collection_name() : string 
    {
        $name = optional_param(Main::NAME, null, PARAM_TEXT);
        if(empty($name)) throw new \Exception('Missing themes collection name.');
        return $name;
    }

    private function get_collection_description() 
    {
        return optional_param(Main::DESCRIPTION, '', PARAM_TEXT);
    }

    private function add_collection() : void 
    {
        global $DB;
        
        if($DB->insert_record('coursework_theme_collections', $this->collection, false))
        {
            $this->log_theme_collections_added();
        }
    }

    private function update_collection() : void 
    {
        global $DB;

        if(empty($this->collection->id))
        {
            throw new \Exception('Missing collection id.');
        }

        if($DB->update_record('coursework_theme_collections', $this->collection))
        {
            $this->log_theme_collections_changed();
        }
    }

    private function log_theme_collections_added() : void 
    {
        $params = array
        (
            'context' => \context_module::instance($this->cm->id)
        );
        
        $event = \mod_coursework\event\theme_collections_added::create($params);
        $event->trigger();
    }

    private function log_theme_collections_changed() : void 
    {
        $params = array
        (
            'context' => \context_module::instance($this->cm->id)
        );
        
        $event = \mod_coursework\event\theme_collections_changed::create($params);
        $event->trigger();
    }


}

