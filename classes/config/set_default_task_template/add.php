<?php

namespace Coursework\Config\SetDefaultTaskTemplate;

class Add extends Action 
{

    function __construct($course, $cm)
    {
        parent::__construct($course, $cm);
    }

    protected function is_task_selected(int $courseId) : bool
    {
        return false;
    }

    protected function get_unique_form_hidden_inputs() : string
    {
        $attr = array(
            'type' => 'hidden',
            'name' => Main::DATABASE_EVENT,
            'value' => Main::ADD_DEFAULT_TASK
        );
        return \html_writer::empty_tag('input', $attr);
    }

}


