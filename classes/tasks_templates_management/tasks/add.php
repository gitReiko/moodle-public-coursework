<?php

namespace Coursework\View\TasksTemplatesManagement\Tasks;

use Coursework\View\TasksTemplatesManagement\Main;

class Add extends Action 
{

    function __construct($course, $cm)
    {
        parent::__construct($course, $cm);
    }

    protected function get_action_header() : string
    {
        return \html_writer::tag('h3', get_string('add_task_header', 'coursework'));
    }

    protected function get_name_input_value() : string
    {
        return '';
    }

    protected function get_description_text() : string
    {
        return '';
    }

    protected function get_action_button() : string
    {
        $attr = array(
            'type' => 'submit',
            'value' => get_string('add_task_template', 'coursework')
        );
        $input = \html_writer::empty_tag('input', $attr);
        return \html_writer::tag('p', $input);
    }

    protected function get_unique_form_hidden_inputs() : string
    {
        $attr = array(
            'type' => 'hidden',
            'name' => Main::DATABASE_EVENT,
            'value' => Main::ADD_TASK
        );
        return \html_writer::empty_tag('input', $attr);
    }

}


