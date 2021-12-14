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
                $this->add_suggested_collection_using($using);
                break;
            case Main::CHANGE_USING_THEMES: 
                $using = $this->get_collection_using();
                $using->id = $this->get_using_row_id();
                $this->change_suggested_collection_using($using);
                break;
            case Main::DELETE_THEME_USING: 
                $this->delete_suggested_collection_using();
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
        $collection = optional_param(Main::COLLECTION_ID, null, PARAM_INT);
        if(empty($collection)) throw new \Exception('Missing collection id.');
        return $collection;
    }

    private function add_suggested_collection_using(\stdClass $using) : void 
    {
        global $DB;
        if($DB->insert_record('coursework_used_collections', $using, false))
        {
            $this->log_themes_using_added();
        }
    }

    private function change_suggested_collection_using(\stdClass $using) : void 
    {
        global $DB;
        if($DB->update_record('coursework_used_collections', $using))
        {
            $this->log_themes_using_changed();
        }
    }

    private function delete_suggested_collection_using() : void 
    {
        global $DB;
        $id = $this->get_using_row_id();
        if($DB->delete_records('coursework_used_collections', array('id'=>$id)))
        {
            $this->log_themes_using_deleted();
        }
    }

    private function get_using_row_id() : int 
    {
        $id = optional_param(Main::THEMES_USING_ID, null, PARAM_INT);
        if(empty($id)) throw new \Exception('Missing themes collection using id.');
        return $id;
    }

    private function log_themes_using_added() : void 
    {
        $params = array
        (
            'context' => \context_module::instance($this->cm->id)
        );
        
        $event = \mod_coursework\event\themes_using_added::create($params);
        $event->trigger();
    }

    private function log_themes_using_changed() : void 
    {
        $params = array
        (
            'context' => \context_module::instance($this->cm->id)
        );
        
        $event = \mod_coursework\event\themes_using_changed::create($params);
        $event->trigger();
    }

    private function log_themes_using_deleted() : void 
    {
        $params = array
        (
            'context' => \context_module::instance($this->cm->id)
        );
        
        $event = \mod_coursework\event\themes_using_deleted::create($params);
        $event->trigger();
    }

}

