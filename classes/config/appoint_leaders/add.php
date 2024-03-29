<?php

namespace Coursework\Config\AppointLeaders;

class Add extends Action 
{

    function __construct($course, $cm)
    {
        parent::__construct($course, $cm);
    }

    protected function get_action_header() : string
    {
        $title = get_string('adding_leader_appoint', 'coursework');

        $attr = array('title' => $title);
        $text = get_string('add_leader_header', 'coursework');
        $text = \html_writer::tag('h3', $text, $attr);

        return StepByStep::get_appoint_adding_explanation($text);
    }

    protected function get_leader_select_html_element() : string
    {
        return $this->get_select($this->courseTeachers, Main::LEADER_ID, null, true);
    }

    protected function get_course_select() : string 
    {
        return $this->get_select($this->siteCourses, Main::COURSE_ID);
    }

    protected function get_quota_input() : string 
    {
        $attr = array(
            'type' => 'number',
            'name' => Main::QUOTA,
            'autocomplete' => 'off',
            'required' => 'required',
            'min' => 1
        );
        return \html_writer::empty_tag('input', $attr);
    }

    protected function get_form_special_params() : string
    {
        $attr = array(
            'type' => 'hidden',
            'name' => Main::DATABASE_EVENT,
            'value' => Main::ADD_LEADER
        );
        $params = \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::GUI_TYPE,
            'value' => Main::OVERVIEW
        );
        $params.= \html_writer::empty_tag('input', $attr);

        return $params;
    }

    protected function get_action_button_text() : string
    {
        return get_string('add_leader', 'coursework');
    }



}


