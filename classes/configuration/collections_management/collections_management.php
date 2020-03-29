<?php

require_once 'collections/collections_overview.php';
require_once 'collections/collections_action.php';
require_once 'collections/collections_add.php';
require_once 'collections/collections_edit.php';
require_once 'collections/database_events_handler.php';
require_once 'themes/themes_management.php';
require_once 'themes/database_events_handler.php';

class CollectionsManagement extends ConfigurationManager
{
    // Types of events
    const OVERVIEW = 'overview';
    const ADD_COLLECTION = 'add_collection';
    const EDIT_COLLECTION = 'edit_collection';
    const THEMES_MANAGEMENT = 'themes_management';
    const ADD_THEME = 'add_theme';
    const EDIT_THEME = 'edit_theme';
    const DELETE_THEME = 'delete_theme';

    function __construct(stdClass $course, stdClass $cm)
    {
        parent::__construct($course, $cm);
    }

    protected function handle_database_event() : void
    {
        if($this->is_database_event_exist())
        {
            $event = optional_param(ConfigurationManager::DATABASE_EVENT, null, PARAM_TEXT);

            switch($event)
            {
                case self::ADD_COLLECTION: 
                case self::EDIT_COLLECTION: 
                    $handler = new CollectionsDBEventsHandler($this->course, $this->cm);
                    $handler->execute(); 
                    break;

                case self::ADD_THEME: 
                case self::EDIT_THEME:
                case self::DELETE_THEME:   
                    $handler = new ThemesDBEventsHandler($this->course, $this->cm);
                    $handler->execute(); 
                    break;  
            }
        }
    }

    protected function get_gui() : string 
    {
        $gui = '';
        $guiType = optional_param(self::GUI_TYPE, null, PARAM_TEXT);

        if($guiType === self::ADD_COLLECTION)
        {
            $gui.= $this->get_add_collection_gui();
        }
        else if($guiType === self::EDIT_COLLECTION)
        {
            $gui.= $this->get_edit_collection_gui();
        }
        else if($guiType === self::THEMES_MANAGEMENT)
        {
            $gui.= $this->get_themes_management_gui();
        }
        else
        {
            $gui.= $this->get_overview_gui();
        }

        return $gui;
    }

    private function get_overview_gui() : string 
    {
        $overview = new CollectionsOverview($this->course, $this->cm);
        return $overview->get_gui();
    }

    private function get_add_collection_gui() : string 
    {
        $addCollection = new CollectionsAdd($this->course, $this->cm);
        return $addCollection->get_gui();
    }

    private function get_edit_collection_gui() : string 
    {
        $editCollection = new CollectionsEdit($this->course, $this->cm);
        return $editCollection->get_gui();
    }

    private function get_themes_management_gui() : string 
    {
        $editCollection = new ThemesManagement($this->course, $this->cm);
        return $editCollection->get_gui();
    }


}
