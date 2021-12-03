<?php

namespace Coursework\Support\LeaderReplacement;

require_once '../../classes/classes_lib/add_edit_template.php';
require_once '../../classes/classes_lib/students_mass_actions.php';
require_once 'overview_leaders.php';
require_once 'replace_leader.php';
require_once 'database.php';

class Main extends \Coursework\ClassesLib\AddEditTemplate
{
    // Types of events
    const OVERVIEW = 'overview';
    const LEADER_REPLACEMENT = 'leader_replacement';
    const STUDENTS = 'students';
    const TEACHER = 'teacher';
    const ID = 'id';

    function __construct(\stdClass $course, \stdClass $cm)
    {
        parent::__construct($course, $cm);
    }

    protected function handle_database_event() : void
    {
        if($this->is_database_event_exist())
        {
            $handler = new Database($this->course, $this->cm);
            $handler->execute();
        }
    }

    protected function get_gui() : string 
    {
        $gui = '';
        $guiType = optional_param(self::GUI_TYPE, null, PARAM_TEXT);

        if($guiType === self::LEADER_REPLACEMENT)
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
        $overview = new OverviewLeaders($this->course, $this->cm);
        return $overview->get_gui();
    }

    private function get_action_gui() : string 
    {
        $distributeStudents = new ReplaceLeader($this->course, $this->cm);
        return $distributeStudents->get_gui();
    }


}
