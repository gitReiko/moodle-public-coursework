<?php

namespace Coursework\Config\SetUsedTaskTemplate;

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
            'value' => Main::ADD_TASK_USING
        );
        return \html_writer::empty_tag('input', $attr);
    }

}


