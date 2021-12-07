<?php

namespace Coursework\Config\DistributeToLeaders;

require_once '../../classes/classes_lib/add_edit_template.php';
require_once '../../classes/classes_lib/students_mass_actions.php';
require_once 'database.php';
require_once 'distribute.php';
require_once 'overview.php';

class Main extends \Coursework\ClassesLib\AddEditTemplate
{
    // Types of events
    const OVERVIEW = 'overview';
    const DISTRIBUTION = 'distribute';
    const EXPAND_QUOTA = 'expand_quota';
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

        if($guiType === self::DISTRIBUTION)
        {
            $gui.= $this->get_distribute_student_gui();
        }
        else
        {
            $gui.= $this->get_overview_gui();
        }

        return $gui;
    }

    private function get_overview_gui() : string 
    {
        $overview = new Overview($this->course, $this->cm);
        return $overview->get_gui();
    }

    private function get_distribute_student_gui() : string 
    {
        $distributeStudents = new Distribute($this->course, $this->cm);
        return $distributeStudents->get_gui();
    }


}
