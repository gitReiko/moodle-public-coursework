<?php

use coursework_lib as lib;
use coursework_students_mass_actions_gui_templates as gui;

class LeaderChangeOverview 
{
    private $course;
    private $cm;

    private $groups;
    private $students;

    const CHANGE_LEADER_FORM = 'change_leader_form';

    function __construct(stdClass $course, stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->groups = groups_get_activity_allowed_groups($cm);
        $this->students = $this->get_students();
    }

    public function get_gui() : string 
    {
        $gui = $this->get_html_form();
        $gui.= $this->get_overview_header();
        $gui.= gui\get_mass_choice_selector($this->groups);
        $gui.= gui\get_students_list($this->students, self::CHANGE_LEADER_FORM);
        $gui.= $this->get_distribute_button();
        
        return $gui;
    }

    private function get_students()
    {
        $students = lib\get_coursework_students_with_groups_leaders_courses($this->cm, $this->groups);
        $students = $this->remove_all_students_without_leader($students);
        return $students;
    }

    private function remove_all_students_without_leader(array $allStudents)
    {
        $studentsWithLeader = array();
        foreach($allStudents as $student)
        {
            if(!empty($student->leader))
            {
                $studentsWithLeader[] = $student;
            }
        }

        return $studentsWithLeader;
    }

    private function get_html_form() : string 
    {
        $form = '<form id="'.self::CHANGE_LEADER_FORM.'">';
        $form.= '<input type="hidden" name="'.CONFIG_MODULE.'" value="'.LEADER_CHANGE.'"/>';
        $form.= '<input type="hidden" name="'.ID.'" value="'.$this->cm->id.'"/>';
        $form.= '<input type="hidden" name="'.ConfigurationManager::GUI_TYPE.'" value="'.LeaderChange::LEADER_CHANGE.'"/>';
        $form.= '</form>';

        return $form;
    }

    private function get_overview_header() : string 
    {
        return '<h3>'.get_string('lc_overview_header', 'coursework').'</h3>';
    }

    private function get_distribute_button() : string 
    {
        $jsfunc = "onclick='return validate_students_mass_action()'";
        return "<button form='".self::CHANGE_LEADER_FORM."' $jsfunc>".get_string('change_leader_for_selected_students', 'coursework').'</button>';
    }


}

