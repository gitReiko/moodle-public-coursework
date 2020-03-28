<?php

require_once 'change_overview.php';
//require_once 'distribute_students.php';
//require_once 'distribution_events_handler.php';

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
            //$handler = new StudentsDistributionDBEventsHandler($this->course, $this->cm);
            //$handler->execute();
        }
    }

    protected function get_gui() : string 
    {
        $gui = '';
        $guiType = optional_param(self::GUI_TYPE, null, PARAM_TEXT);

        if($guiType === self::LEADER_CHANGE)
        {
            //$gui.= $this->get_distribute_student_gui();
            $gui.= 'dsvs';
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

    private function get_distribute_student_gui() : string 
    {
        $distributeStudents = new DistributeStudents($this->course, $this->cm);
        return $distributeStudents->get_gui();
    }


}
