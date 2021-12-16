<?php

namespace Coursework\Config\DistributeToLeaders;

require_once '../../classes/classes_lib/add_edit_template.php';
require_once '../../classes/classes_lib/students_mass_actions.php';
require_once 'database.php';
require_once 'distribute.php';
require_once 'overview.php';

class Main extends \Coursework\ClassesLib\AddEditTemplate
{
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

    protected function execute_database_handler() : void
    {
        $handler = new Database($this->course, $this->cm);
        $handler->execute();
    }

    protected function redirect_to_prevent_page_update() : void
    {
        $path = '/mod/coursework/pages/config/distribute_to_leaders.php';
        $params = array('id'=>$this->cm->id);
        redirect(new \moodle_url($path, $params));
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
