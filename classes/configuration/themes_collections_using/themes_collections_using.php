<?php

require_once 'add_new_collection_using.php';
require_once 'database_events_handler.php';
require_once 'using_collections_overview.php';

class ThemesCollectionsUsing extends ConfigurationManager
{
    const OVERVIEW = 'overview';
    const ADD_THEME_USING = 'add_theme_using';
    const DELETE_THEME_USING = 'delete_theme_using';

    function __construct(stdClass $course, stdClass $cm)
    {
        parent::__construct($course, $cm);
    }

    protected function handle_database_event() : void
    {
        if($this->is_database_event_exist())
        {
            $handler = new CollectionsUsingDBEventsHandler($this->course, $this->cm);
            $handler->execute();
        }
    }

    protected function get_gui() : string 
    {
        $gui = '';
        $guiType = optional_param(self::GUI_TYPE, null, PARAM_TEXT);

        if($guiType === self::ADD_THEME_USING)
        {
            $gui.= $this->get_add_theme_using_gui();
        }
        else
        {
            $gui.= $this->get_overview_gui();
        }

        return $gui;
    }

    private function get_overview_gui() : string 
    {
        $overview = new UsingCollectionsOverview($this->course, $this->cm);
        return $overview->get_gui();
    }

    private function get_add_theme_using_gui() : string 
    {
        $add = new AddNewCollectionUsing($this->course, $this->cm);
        return $add->get_gui();
    }





}
