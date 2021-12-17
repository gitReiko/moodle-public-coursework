<?php

namespace Coursework\View\ThemesCollectionsManagement\Collections;

use Coursework\View\ThemesCollectionsManagement\Main;

class Add extends Action 
{

    function __construct($course, $cm)
    {
        parent::__construct($course, $cm);
    }

    protected function get_action_header() : string
    {
        $text = get_string('add_collection_header', 'coursework');
        return \html_writer::tag('h3', $text);
    }

    protected function get_name_input_value() : string
    {
        return '';
    }

    protected function is_course_selected(int $courseId) : bool
    {
        return false;
    }

    protected function get_description_text() : string
    {
        return '';
    }

    protected function get_action_button() : string
    {
        $attr = array(
            'type' => 'submit',
            'value' => get_string('add_collection', 'coursework')
        );
        $btn = \html_writer::empty_tag('input', $attr);
        return \html_writer::tag('p', $btn);
    }

    protected function get_unique_form_hidden_inputs() : string
    {
        $attr = array(
            'type' => 'hidden',
            'name' => Main::DATABASE_EVENT,
            'value' => Main::ADD_COLLECTION
        );
        return \html_writer::empty_tag('input', $attr);
    }

}


