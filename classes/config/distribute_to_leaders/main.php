<?php

namespace Coursework\Config\DistributeToLeaders;

require_once '../../classes/lib/main_template.php';
require_once '../../classes/lib/students_mass_actions.php';
require_once 'database.php';
require_once 'distribute.php';
require_once 'overview.php';
require_once 'step_by_step.php';

class Main extends \Coursework\Classes\Lib\MainTemplate
{
    const MODULE_URL = '/mod/coursework/pages/config/distribute_to_leaders.php';

    const OVERVIEW = 'overview';
    const DISTRIBUTION = 'distribute';

    const ID = 'id';
    const TEACHER = 'teacher';
    const COURSE = 'course';
    const EXPAND_QUOTA = 'expand_quota';

    function __construct(\stdClass $course, \stdClass $cm)
    {
        parent::__construct($course, $cm);
    }

    protected function execute_database_handler() 
    {
        $handler = new Database($this->course, $this->cm);
        return $handler->execute();
    }

    protected function get_redirect_path() : string
    {
        return '/mod/coursework/pages/config/distribute_to_leaders.php';
    }

    protected function get_redirect_params() : array
    {
        return array('id' => $this->cm->id);
    }

    protected function get_content() : string 
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
