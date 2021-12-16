<?php

namespace Coursework\Config\AppointLeaders;

require_once '../../classes/lib/add_edit_template.php';
require_once 'overview_leaders.php';
require_once 'leader_action.php';
require_once 'add_leader.php';
require_once 'edit_leader.php';
require_once 'database.php';

class Main extends \Coursework\Classes\Lib\AddEditTemplate
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

    protected function execute_database_handler() : void
    {
        $handler = new Database($this->course, $this->cm);
        $handler->execute();
    }

    protected function redirect_to_prevent_page_update() : void
    {
        $path = '/mod/coursework/pages/config/appoint_leaders.php';
        $params = array('id'=>$this->cm->id);
        redirect(new \moodle_url($path, $params));
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
        $overview = new OverviewLeaders($this->course, $this->cm);
        return $overview->get_gui();
    }

    private function get_add_leader_gui() : string 
    {
        $addGUI = new AddLeader($this->course, $this->cm);
        return $addGUI->display();
    }

    private function get_edit_leader_gui() : string 
    {
        $editGUI = new EditLeader($this->course, $this->cm);
        return $editGUI->display();
    }


}
