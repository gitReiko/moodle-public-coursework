<?php 

use coursework_lib as lib;

class ThemesDBEventsHandler 
{
    private $course;
    private $cm;

    function __construct(stdClass $course, stdClass $cm) 
    {
        $this->course = $course;
        $this->cm = $cm;
    }

    public function execute() : void 
    {
        $event = optional_param(ConfigurationManager::DATABASE_EVENT, null, PARAM_TEXT);

        switch($event)
        {
            case CollectionsManagement::ADD_THEME: 
                $theme = $this->get_theme();
                $this->add_theme($theme);
                break;
            case CollectionsManagement::EDIT_THEME: 
                $theme = $this->get_theme();
                $theme->id = $this->get_theme_id();
                $this->update_theme($theme);
                break;
            case CollectionsManagement::DELETE_THEME: 
                $this->delete_theme();
                break;
        }
    }

    private function get_theme() : stdClass 
    {
        $theme = new stdClass;
        $theme->name = $this->get_theme_name();
        $theme->collection = $this->get_theme_collection();
        return $theme;
    }

    private function get_theme_id() : int 
    {
        $id = optional_param(THEME.ID, null, PARAM_INT);
        if(empty($id)) throw new Exception('Missing theme id.');
        return $id;
    }

    private function get_theme_name() : string 
    {
        $name = optional_param(NAME, null, PARAM_TEXT);
        if(empty($name)) throw new Exception('Missing theme name.');
        return $name;
    }

    private function get_theme_collection() : int 
    {
        $collectionId = optional_param(COLLECTION.ID, null, PARAM_INT);
        if(empty($collectionId)) throw new Exception('Missing theme collection id.');
        return $collectionId;
    }

    private function add_theme(stdClass $theme) : void 
    {
        global $DB;
        $DB->insert_record('coursework_themes', $theme, false);
    }

    private function update_theme(stdClass $theme) : void 
    {
        global $DB;
        $DB->update_record('coursework_themes', $theme);
    }

    private function delete_theme() : void 
    {
        global $DB;
        $DB->delete_records('coursework_themes', array('id'=>$this->get_theme_id()));
    }

}

