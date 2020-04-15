<?php

require_once 'database_events_handler.php';
require_once 'task_using_overview.php';
require_once 'task_using_action.php';
require_once 'task_using_add.php';
require_once 'task_using_edit.php';

class TasksUsingMain extends ConfigurationManager
{
    // Types of events
    const OVERVIEW = 'overview';
    const ADD_TASK_USING = 'add_task_using';
    const EDIT_TASK_USING = 'edit_task_using';

    function __construct(stdClass $course, stdClass $cm)
    {
        parent::__construct($course, $cm);
    }

    protected function handle_database_event() : void
    {
        if($this->is_database_event_exist())
        {
            $handler = new TaskUsingDBEventsHandler($this->course, $this->cm);
            $handler->execute(); 
        }
    }

    protected function get_gui() : string 
    {
        $gui = '';
        $guiType = optional_param(self::GUI_TYPE, null, PARAM_TEXT);

        if($guiType === self::ADD_TASK_USING)
        {
            $gui.= $this->get_add_task_using_gui();
        }
        else if($guiType === self::EDIT_TASK_USING)
        {
            $gui.= $this->get_edit_task_using_gui();
        }
        else
        {
            $gui.= $this->get_overview_gui();
        }

        return $gui;
    }

 
    private function get_overview_gui() : string 
    {
        $overview = new TasksUsingOverview($this->course, $this->cm);
        return $overview->get_gui();
    }

    private function get_add_task_using_gui() : string 
    {
        $add = new TaskUsingAdd($this->course, $this->cm);
        return $add->get_gui();
    }

    private function get_edit_task_using_gui() : string 
    {
        $edit = new TaskUsingEdit($this->course, $this->cm);
        return $edit->get_gui();
    }

}
