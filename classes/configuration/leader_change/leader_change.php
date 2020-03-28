<?php

require_once 'change_overview.php';
require_once 'change_action.php';
require_once 'change_events_handler.php';

class LeaderChange extends ConfigurationManager
{
    // Types of events
    const OVERVIEW = 'overview';
    const LEADER_CHANGE = 'leader_change';

    function __construct(stdClass $course, stdClass $cm)
    {
        parent::__construct($course, $cm);
    }

    protected function handle_database_event() : void
    {
        if($this->is_database_event_exist())
        {
            $handler = new ChangeLeaderDBEventsHandler($this->course, $this->cm);
            $handler->execute();
        }
    }

    protected function get_gui() : string 
    {
        $gui = '';
        $guiType = optional_param(self::GUI_TYPE, null, PARAM_TEXT);

        if($guiType === self::LEADER_CHANGE)
        {
            $gui.= $this->get_action_gui();
        }
        else
        {
            $gui.= $this->get_overview_gui();
        }

        return $gui;
    }

    private function get_overview_gui() : string 
    {
        $overview = new LeaderChangeOverview($this->course, $this->cm);
        return $overview->get_gui();
    }

    private function get_action_gui() : string 
    {
        $distributeStudents = new LeaderChangeAction($this->course, $this->cm);
        return $distributeStudents->get_gui();
    }


}
