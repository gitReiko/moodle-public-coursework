<?php

namespace Coursework\Config\LeadersSetting;

require_once '../classes/config/add_edit_template.php';
require_once 'leaders_overview_gui.php';
require_once 'leaders_action_gui.php';
require_once 'leaders_add_gui.php';
require_once 'leaders_edit_gui.php';
require_once 'leaders_events_handler.php';

class Main extends \Coursework\Config\AddEditTemplate
{
    // Types of events
    const OVERVIEW = 'overview';
    const ADD_LEADER = 'add_leader';
    const EDIT_LEADER = 'edit_leader';
    const DELETE_LEADER = 'delete_leader';
    const LEADER_ROW_ID = 'leader_row_id';

    // post consts
    const COURSE_MODULE_ID = 'id';
    const LEADER_ID = 'leader_id';
    const COURSE_ID = 'course_id';
    const QUOTA = 'quota';

    function __construct(\stdClass $course, \stdClass $cm)
    {
        parent::__construct($course, $cm);
    }

    protected function handle_database_event() : void
    {
        if($this->is_database_event_exist())
        {
            $handler = new LeadersEventsHandler($this->course, $this->cm);
            $handler->execute();
        }
    }

    protected function get_gui() : string 
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
