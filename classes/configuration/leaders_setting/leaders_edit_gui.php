<?php

class LeadersEditGUI extends LeadersActionGUI 
{
    private $id;
    private $leader;
    private $course_;
    private $quota;

    function __construct($course, $cm)
    {
        parent::__construct($course, $cm);

        $this->id = optional_param(LeadersSetting::LEADER_ROW_ID, null, PARAM_INT);
        $this->leader = optional_param(TEACHER.ID, null, PARAM_INT);
        $this->course_ = optional_param(COURSE.ID, null, PARAM_INT);
        $this->quota = optional_param(QUOTA.ID, null, PARAM_INT);
    }

    protected function get_action_header() : string
    {
        return '<h3>'.get_string('edit_leader_header', 'coursework').'</h3>';
    }

    protected function get_leader_select() : string 
    {
        return $this->get_select($this->courseTeachers, TEACHER, $this->leader);
    }

    protected function get_course_select() : string 
    {
        return $this->get_select($this->siteCourses, COURSE, $this->course_);
    }

    protected function get_quota_input() : string 
    {
        $quota = '<input type="number" name="'.QUOTA.'" value="'.$this->quota.'" ';
        $quota.= 'autocomplete="off" required min="1">';
        return $quota;
    }

    protected function get_form_special_params() : string
    {
        $params = '<input type="hidden" name="'.LeadersSetting::DATABASE_EVENT.'" value="'.LeadersSetting::EDIT_LEADER.'">';
        $params.= '<input type="hidden" name="'.LeadersSetting::GUI_TYPE.'" value="'.LeadersSetting::OVERVIEW.'">';
        $params.= '<input type="hidden" name="'.LeadersSetting::LEADER_ROW_ID.'" value="'.$this->id.'">';
        return $params;
    }

    protected function get_action_button() : string
    {
        $btn = '<input type="submit" form="'.LeadersActionGUI::ACTION_FORM.'" ';
        $btn.= 'value="'.get_string('save_changes', 'coursework').'" >';
        return $btn;
    }



}

