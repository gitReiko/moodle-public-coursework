<?php

require_once 'leaders_overview_gui.php';
require_once 'leaders_action_gui.php';
require_once 'leaders_add_gui.php';
require_once 'leaders_edit_gui.php';
require_once 'leaders_events_handler.php';

class LeadersSetting
{
    const DATABASE_EVENT = 'database_event';
    const GUI_TYPE = 'gui_type';

    // Types of events and gui
    const OVERVIEW = 'overview';
    const ADD_LEADER = 'add_leader';
    const EDIT_LEADER = 'edit_leader';
    const DELETE_LEADER = 'delete_leader';
    const LEADER_ROW_ID = 'leader_row_id';

    private $course;
    private $cm;

    function __construct(stdClass $course, stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;
    }

    public function execute() : string 
    {
        $this->handle_database_event();
        return $this->get_gui();
    }

    private function handle_database_event() : void
    {
        if($this->is_database_event_exist())
        {
            $handler = new LeadersEventsHandler($this->course, $this->cm);
            $handler->execute();
        }
    }

    private function is_database_event_exist() : bool 
    {
        $event = optional_param(self::DATABASE_EVENT, null, PARAM_TEXT);

        if(isset($event)) return true;
        else return false;
    }

    private function get_gui() : string 
    {
        $gui = '';
        $guiType = optional_param(self::GUI_TYPE, null, PARAM_TEXT);

        if($guiType === self::ADD_LEADER)
        {
            $gui.= $this->get_add_leader_gui();
        }
        else if($guiType === self::EDIT_LEADER)
        {
            $gui.= $this->get_edit_leader_gui();
        }
        else
        {
            $gui.= $this->get_overview_gui();
        }

        return $gui;
    }

    private function get_overview_gui() : string 
    {
        $overview = new LeadersOverviewGUI($this->course, $this->cm);
        return $overview->get_gui();
    }

    private function get_add_leader_gui() : string 
    {
        $addGUI = new LeadersAddGUI($this->course, $this->cm);
        return $addGUI->display();
    }

    private function get_edit_leader_gui() : string 
    {
        $editGUI = new LeadersEditGUI($this->course, $this->cm);
        return $editGUI->display();
    }


}
