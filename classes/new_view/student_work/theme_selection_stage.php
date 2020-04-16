<?php

use coursework_lib as lib;

class ThemeSelectionStage 
{
    private $course;
    private $cm;
    private $studentId;

    function __construct(stdClass $course, stdClass $cm, int $studentId)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->studentId = $studentId;
    }

    public function get_gui() : string 
    {
        if($this->is_observer_student())
        {
            return $this->get_theme_selection_page();
        }
        else 
        {
            return $this->interaction_with_student_work_will_be_available_after_theme_selection();
        }
    }

    private function is_observer_student() : bool 
    {
        global $USER;
        if($this->studentId == $USER->id) return true;
        else return false;
    }

    private function interaction_with_student_work_will_be_available_after_theme_selection() : string 
    {
        return '<p>'.get_string('interaction_with_student_work_will_be_available_after_theme_selection', 'coursework').'</p>';
    }

    private function get_theme_selection_page() : string 
    {
        $page = 'theme selectionnnnnnn';

        return $page;
    }






}
