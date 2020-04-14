<?php

require_once 'tasks/database_events_handler.php';
require_once 'tasks/tasks_overview.php';
require_once 'tasks/task_action.php';
require_once 'tasks/task_add.php';
require_once 'tasks/task_edit.php';

require_once 'sections/sections_overview.php';

require_once 'locallib.php';

class TasksManagement extends ConfigurationManager
{
    // Types of events
    const OVERVIEW = 'overview';
    const ADD_TASK = 'add_task';
    const EDIT_TASK = 'edit_task';
    const SECTIONS_MANAGEMENT = 'sections_management';
    const ADD_SECTION = 'add_section';
    const EDIT_SECTION = 'edit_section';
    const DELETE_SECTION = 'delete_section';

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
                case self::ADD_TASK: 
                case self::EDIT_TASK: 
                    $handler = new TasksDBEventsHandler($this->course, $this->cm);
                    $handler->execute();
                    break;

                case self::ADD_SECTION: 
                case self::EDIT_SECTION:
                case self::DELETE_SECTION:   
                    //$handler = new ThemesDBEventsHandler($this->course, $this->cm);
                    //$handler->execute(); 
                    break;  
            }
        }
    }

    protected function get_gui() : string 
    {
        $gui = '';
        $guiType = optional_param(self::GUI_TYPE, null, PARAM_TEXT);

        if($guiType === self::ADD_TASK)
        {
            $gui.= $this->get_add_task_gui();
        }
        else if($guiType === self::EDIT_TASK)
        {
            $gui.= $this->get_edit_task_gui();
        }
        else if($guiType === self::SECTIONS_MANAGEMENT)
        {
            $gui.= $this->get_sections_management_gui();
        }
        else
        {
            $gui.= $this->get_overview_gui();
        }

        return $gui;
    }

   
    private function get_overview_gui() : string 
    {
        $overview = new TasksOverview($this->course, $this->cm);
        return $overview->get_gui();
    }

    private function get_add_task_gui() : string 
    {
        $add = new TaskAdd($this->course, $this->cm);
        return $add->get_gui();
    }

    private function get_edit_task_gui() : string 
    {
        $edit = new TaskEdit($this->course, $this->cm);
        return $edit->get_gui();
    }

    private function get_sections_management_gui() : string 
    {
        $sectionsOverview = new TasksSectionsOverview($this->course, $this->cm);
        return $sectionsOverview->get_gui();
    }


}
