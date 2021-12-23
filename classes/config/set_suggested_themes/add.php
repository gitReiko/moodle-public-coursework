<?php

namespace Coursework\Config\SetSuggestedThemes;

class Add extends Action 
{

    function __construct(\stdClass $course, \stdClass $cm)
    {
        parent::__construct($course, $cm);
    }

    protected function get_action_header() : string 
    {
        $text = get_string('add_suggested_themes_collection', 'coursework');
        return \html_writer::tag('h3', $text);
    }

    protected function is_task_selected(int $collectionId) : bool
    {
        return false;
    }

    protected function get_default_count_of_same_themes() : int
    {
        return 1;
    }

    protected function get_action_button() : string 
    {
        $attr = array(
            'type' => 'submit',
            'value' => get_string('add_suggested_themes', 'coursework'),
            'form' => self::ACTION_FORM
        );
        return \html_writer::empty_tag('input', $attr);
    }

    protected function get_neccessary_input_params() : string 
    {
        $attr = array(
            'type' => 'hidden',
            'name' => Main::DATABASE_EVENT,
            'value' => Main::ADD_THEME_USING,
            'form' => self::ACTION_FORM
        );
        return \html_writer::empty_tag('input', $attr);
    }

}
