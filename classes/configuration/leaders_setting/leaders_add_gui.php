<?php

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
        $select = '<select name="'.TEACHER.'" autocomplete="off">';
        foreach($this->courseTeachers as $teacher)
        {
            $select.= '<option value="'.$teacher->id.'">';
            $select.= $teacher->fullname;
            $select.= '</option>';
        }
        $select.= '</select>';
        return $select;
    }







}


