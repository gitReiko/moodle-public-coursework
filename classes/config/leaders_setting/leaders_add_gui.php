<?php

namespace Coursework\Config\LeadersSetting;

class LeadersAddGUI extends LeadersActionGUI 
{

    function __construct($course, $cm)
    {
        parent::__construct($course, $cm);
    }

    protected function get_action_header() : string
    {
        return \html_writer::tag('h3', get_string('add_leader_header', 'coursework'));
    }

    protected function get_leader_select() : string 
    {
        return $this->get_select($this->courseTeachers, TEACHER, null, true);
    }

    protected function get_course_select() : string 
    {
        return $this->get_select($this->siteCourses, COURSE);
    }

    protected function get_quota_input() : string 
    {
        $attr = array(
            'type' => 'number',
            'name' => QUOTA,
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

    protected function get_action_button() : string
    {
        $attr = array(
            'type' => 'submit',
            'form' => LeadersActionGUI::ACTION_FORM,
            'value' => get_string('add_leader', 'coursework')
        );
        return \html_writer::empty_tag('input', $attr);
    }



}


