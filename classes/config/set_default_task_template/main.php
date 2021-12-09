<?php

namespace Coursework\Config\SetDefaultTaskTemplate;

require_once '../../classes/classes_lib/add_edit_template.php';
require_once 'action.php';
require_once 'add.php';
require_once 'database.php';
require_once 'edit.php';
require_once 'overview.php';

class Main extends \Coursework\ClassesLib\AddEditTemplate
{
    const OVERVIEW = 'overview';
    const ADD_DEFAULT_TASK = 'add_default_task';
    const EDIT_DEFAULT_TASK = 'edit_default_task';

    const ID = 'id';
    const DEFAULT_TASK_ROW_ID = 'default_task_row_id';
    const TASK = 'task';

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

        if($guiType === self::ADD_DEFAULT_TASK)
        {
            $gui.= $this->get_add();
        }
        else if($guiType === self::EDIT_DEFAULT_TASK)
        {
            $gui.= $this->get_edit();
        }
        else
        {
            $gui.= $this->get_overview();
        }

        return $gui;
    }
 
    private function get_overview() : string 
    {
        $overview = new Overview($this->course, $this->cm);
        return $overview->get_gui();
    }

    private function get_add() : string 
    {
        $add = new Add($this->course, $this->cm);
        return $add->get_gui();
    }

    private function get_edit() : string 
    {
        $edit = new Edit($this->course, $this->cm);
        return $edit->get_gui();
    }

}
