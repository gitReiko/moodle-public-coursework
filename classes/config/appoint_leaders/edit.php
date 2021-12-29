<?php

namespace Coursework\Config\AppointLeaders;

class Edit extends Action 
{
    private $id;
    private $leader;
    private $course_;
    private $quota;

    function __construct($course, $cm)
    {
        parent::__construct($course, $cm);

        $this->id = optional_param(Main::LEADER_ROW_ID, null, PARAM_INT);
        $this->leader = optional_param(Main::LEADER_ID, null, PARAM_INT);
        $this->course_ = optional_param(Main::COURSE_ID, null, PARAM_INT);
        $this->quota = optional_param(Main::QUOTA, null, PARAM_INT);
    }

    protected function get_action_header() : string
    {
        return \html_writer::tag('h3', get_string('edit_leader_header', 'coursework'));
    }

    protected function get_leader_select() : string 
    {
        return $this->get_select($this->courseTeachers, Main::LEADER_ID, $this->leader, true);
    }

    protected function get_course_select() : string 
    {
        return $this->get_select($this->siteCourses, Main::COURSE_ID, $this->course_);
    }

    protected function get_quota_input() : string 
    {
        $attr = array(
            'type' => 'number',
            'name' => Main::QUOTA,
            'value' => $this->quota,
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
            'value' => Main::EDIT_LEADER
        );
        $params = \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::GUI_TYPE,
            'value' => Main::OVERVIEW
        );
        $params.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::LEADER_ROW_ID,
            'value' => $this->id
        );
        $params.= \html_writer::empty_tag('input', $attr);

        return $params;
    }

    protected function get_action_button() : string
    {
        $attr = array(
            'type' => 'submit',
            'form' => Action::ACTION_FORM,
            'value' => get_string('save_changes', 'coursework')
        );
        return \html_writer::empty_tag('input', $attr);
    }



}

