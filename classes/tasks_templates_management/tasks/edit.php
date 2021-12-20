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
        return \html_writer::tag('h3', get_string('edit_task_header', 'coursework'));
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
        $attr = array(
            'type' => 'submit',
            'value' => get_string('save_changes', 'coursework')
        );
        $input = \html_writer::empty_tag('input', $attr);
        return \html_writer::tag('p', $input);
    }

    protected function get_unique_form_hidden_inputs() : string
    {
        $attr = array(
            'type' => 'hidden',
            'name' => Main::DATABASE_EVENT,
            'value' => Main::EDIT_TASK
        );
        $inputs = \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::TASK_ID,
            'value' => $this->task->id
        );
        $inputs.= \html_writer::empty_tag('input', $attr);

        return $inputs;
    }

}


