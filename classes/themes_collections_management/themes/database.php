<?php

namespace Coursework\View\ThemesCollectionsManagement\Themes;

use Coursework\View\ThemesCollectionsManagement\Main;

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
            case Main::ADD_THEME: 
                $theme = $this->get_theme();
                $this->add_theme($theme);
                break;
            case Main::EDIT_THEME: 
                $theme = $this->get_theme();
                $theme->id = $this->get_theme_id();
                $this->update_theme($theme);
                break;
            case Main::DELETE_THEME: 
                $this->delete_theme();
                break;
        }
    }

    private function get_theme() : \stdClass 
    {
        $theme = new \stdClass;
        $theme->content = $this->get_theme_content();
        $theme->collection = $this->get_theme_collection();
        return $theme;
    }

    private function get_theme_id() : int 
    {
        $id = optional_param(Main::THEME_ID, null, PARAM_INT);
        if(empty($id)) throw new \Exception('Missing theme id.');
        return $id;
    }

    private function get_theme_content() : string 
    {
        $name = optional_param(Main::CONTENT, null, PARAM_TEXT);
        if(empty($name)) throw new \Exception('Missing theme name.');
        return $name;
    }

    private function get_theme_collection() : int 
    {
        $collectionId = optional_param(Main::COLLECTION_ID, null, PARAM_INT);
        if(empty($collectionId)) throw new \Exception('Missing theme collection id.');
        return $collectionId;
    }

    private function add_theme(\stdClass $theme) : void 
    {
        global $DB;
        if($DB->insert_record('coursework_themes', $theme, false))
        {
            $this->log_theme_added();
        }
    }

    private function update_theme(\stdClass $theme) : void 
    {
        global $DB;
        if($DB->update_record('coursework_themes', $theme))
        {
            $this->log_theme_changed();
        }
    }

    private function delete_theme() : void 
    {
        global $DB;
        if($DB->delete_records('coursework_themes', array('id'=>$this->get_theme_id())))
        {
            $this->log_theme_deleted();
        }
    }

    private function log_theme_added() : void 
    {
        $params = array
        (
            'context' => \context_module::instance($this->cm->id)
        );
        
        $event = \mod_coursework\event\theme_added::create($params);
        $event->trigger();
    }

    private function log_theme_changed() : void 
    {
        $params = array
        (
            'context' => \context_module::instance($this->cm->id)
        );
        
        $event = \mod_coursework\event\theme_changed::create($params);
        $event->trigger();
    }

    private function log_theme_deleted() : void 
    {
        $params = array
        (
            'context' => \context_module::instance($this->cm->id)
        );
        
        $event = \mod_coursework\event\theme_deleted::create($params);
        $event->trigger();
    }

}

