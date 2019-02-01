<?php

require_once 'classes/participants_management.php';
require_once 'classes/themes_management.php';
require_once 'classes/students_assignment.php';

class CourseworkConfiguration
{
    private $course;
    private $cm;
    private $module;

    function __construct($course, $cm)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->init_module_variable();
    }

    private function init_module_variable() : void
    {
        $this->module = optional_param(ECM_MODULE, 0 , PARAM_TEXT);
        if(empty($this->module)) $this->module = PARTICIPANT_MANAGEMENT;
    }

    public function display() : void
    {
        $str = '<h2>'.get_string('coursework_configuration', 'coursework').'</h2>';
        $str.= $this->coursework_gui_begin();

        if($this->module === PARTICIPANT_MANAGEMENT)
        {
            $participants = new ParticipantsManagement($this->course, $this->cm);
            $str .= $participants->execute();
        }
        else if($this->module === THEMES_MANAGEMENT)
        {
            $themes = new ThemesManagement($this->course, $this->cm);
            $str .= $themes->display();
        }
        else if($this->module === STUDENTS_ASSIGNMENT)
        {
            $assignment = new StudentsAssignment($this->course, $this->cm);
            $str .= $assignment->execute();
        }

        $str.= $this->coursework_gui_end();

        echo $str;
    }

    private function coursework_gui_begin() : string
    {
        $str = '<div class="coursework-configuration">';
        $str.= '<ol class="coursework-configuration">';

        if($this->module === PARTICIPANT_MANAGEMENT) $str.= '<li class="selected">';
        else $str.= '<li onclick="change_bookmark(`'.PARTICIPANT_MANAGEMENT.'`)">';
        $str.= get_string('participants_enrollment', 'coursework').'</li>';

        if($this->module === THEMES_MANAGEMENT) $str .= '<li class="selected">';
        else $str.= '<li onclick="change_bookmark(`'.THEMES_MANAGEMENT.'`)">';
        $str.= get_string('themes_management', 'coursework').'</li>';

        if($this->module === STUDENTS_ASSIGNMENT) $str .= '<li class="selected">';
        else $str.= '<li onclick="change_bookmark(`'.STUDENTS_ASSIGNMENT.'`)">';
        $str.= get_string('students_assignment', 'coursework').'</li>';

        $str.= '</ol>';
        $str.= $this->coursework_gui_forms();

        $str.= '<div style="padding: 10px;">';

        return $str;
    }

    private function coursework_gui_end() : string { return '</div></div>'; }

    private function coursework_gui_forms() : string
    {
        $str = '<form id="'.PARTICIPANT_MANAGEMENT.'">';
        $str.= '<input type="hidden" name="'.ECM_MODULE.'" value="'.PARTICIPANT_MANAGEMENT.'"/>';
        $str.= '<input type="hidden" name="id" value="'.$this->cm->id.'"/>';
        $str.= '</form>';
        $str.= '<form id="'.THEMES_MANAGEMENT.'">';
        $str.= '<input type="hidden" name="'.ECM_MODULE.'" value="'.THEMES_MANAGEMENT.'"/>';
        $str.= '<input type="hidden" name="id" value="'.$this->cm->id.'"/>';
        $str.= '</form>';
        $str.= '<form id="'.STUDENTS_ASSIGNMENT.'">';
        $str.= '<input type="hidden" name="'.ECM_MODULE.'" value="'.STUDENTS_ASSIGNMENT.'"/>';
        $str.= '<input type="hidden" name="id" value="'.$this->cm->id.'"/>';
        $str.= '</form>';
        return $str;
    }

}
