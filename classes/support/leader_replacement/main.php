<?php

namespace Coursework\Support\LeaderReplacement;

require_once '../../classes/lib/main_template.php';
require_once '../../classes/lib/students_mass_actions.php';
require_once 'database.php';
require_once 'overview.php';
require_once 'replace.php';

class Main extends \Coursework\Classes\Lib\MainTemplate
{
    const MODULE_URL = '/mod/coursework/pages/support/leader_replacement.php';

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

    protected function execute_database_handler() 
    {
        $handler = new Database($this->course, $this->cm);
        $handler->execute();
    }

    protected function get_redirect_path() : string
    {
        return '/mod/coursework/pages/support/leader_replacement.php';
    }

    protected function get_redirect_params() : array
    {
        return array('id' => $this->cm->id);
    }

    protected function get_content() : string 
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
        $overview = new Overview($this->course, $this->cm);
        return $overview->get_gui();
    }

    private function get_action_gui() : string 
    {
        $distributeStudents = new Replace($this->course, $this->cm);
        return $distributeStudents->get_gui();
    }


}
