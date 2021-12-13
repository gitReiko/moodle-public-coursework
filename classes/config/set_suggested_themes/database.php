<?php 

namespace Coursework\Config\SetSuggestedThemes;

class Database 
{
    private $course;
    private $cm;

    function __construct(\stdClass $course, \stdClass $cm) 
    {
        $this->course = $course;
        $this->cm = $cm;
    }

    public function execute() : void 
    {
        $event = optional_param(Main::DATABASE_EVENT, null, PARAM_TEXT);

        switch($event)
        {
            case Main::ADD_THEME_USING: 
                $using = $this->get_collection_using();
                $this->add_collection_using($using);
                break;
            case Main::DELETE_THEME_USING: 
                $this->delete_collection_using();
                break;
        }
    }

    private function get_collection_using() : \stdClass 
    {
        $using = new \stdClass;
        $using->coursework = $this->cm->instance;
        $using->collection = $this->get_collection();
        return $using;
    }

    private function get_collection() : int 
    {
        $collection = optional_param(COLLECTION, null, PARAM_INT);
        if(empty($collection)) throw new \Exception('Missing collection id.');
        return $collection;
    }

    private function add_collection_using(\stdClass $using) : void 
    {
        global $DB;
        $DB->insert_record('coursework_used_collections', $using, false);
    }

    private function delete_collection_using() : void 
    {
        global $DB;
        $id = $this->get_using_row_id();
        $DB->delete_records('coursework_used_collections', array('id'=>$id));
    }

    private function get_using_row_id() : int 
    {
        $id = optional_param(COLLECTION.ROW.ID, null, PARAM_INT);
        if(empty($id)) throw new \Exception('Missing collection id.');
        return $id;
    }



}

