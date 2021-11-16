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
        return '<h3>'.get_string('add_leader_header', 'coursework').'</h3>';
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
        $quota = '<input type="number" name="'.QUOTA.'"';
        $quota.= 'autocomplete="off" required min="1">';
        return $quota;
    }

    protected function get_form_special_params() : string
    {
        $params = '<input type="hidden" name="'.Main::DATABASE_EVENT.'" value="'.Main::ADD_LEADER.'">';
        $params.= '<input type="hidden" name="'.Main::GUI_TYPE.'" value="'.Main::OVERVIEW.'">';
        return $params;
    }

    protected function get_action_button() : string
    {
        $btn = '<input type="submit" form="'.LeadersActionGUI::ACTION_FORM.'" ';
        $btn.= 'value="'.get_string('add_leader', 'coursework').'" >';
        return $btn;
    }



}


