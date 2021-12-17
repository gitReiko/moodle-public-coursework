<?php

namespace Coursework\View\TasksTemplatesManagement\Tasks;

use Coursework\View\TasksTemplatesManagement\Main;
use Coursework\View\TasksTemplatesManagement\Lib;

class Edit extends Action 
{
    private $task;

    function __construct($course, $cm)
    {
        parent::__construct($course, $cm);

        $this->task = Lib::get_task_from_post();
    }

    protected function get_action_header() : string
    {
        return '<h3>'.get_string('edit_task_header', 'coursework').'</h3>';
    }

    protected function get_name_input_value() : string
    {
        return $this->task->name;
    }

    protected function get_description_text() : string
    {
        return $this->task->description;
    }

    protected function get_action_button() : string
    {
        return '<p><input type="submit" value="'.get_string('save_changes', 'coursework').'" ></p>';
    }

    protected function get_unique_form_hidden_inputs() : string
    {
        $inputs = '<input type="hidden" name="'.Main::DATABASE_EVENT.'" value="'.Main::EDIT_TASK.'"/>';
        $inputs.= '<input type="hidden" name="'.Main::TASK_ID.'" value="'.$this->task->id.'">';
        return $inputs;
    }

}


