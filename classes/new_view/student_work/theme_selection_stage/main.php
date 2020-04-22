<?php

require_once 'not_available_page.php';
require_once 'theme_selection_page.php';

use coursework_lib as lib;

class ThemeSelectionStageMain 
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
        else if($this->is_task_used_in_coursework_instance())
        {
            return 'task ';
        }
    }

    private function is_observer_student() : bool 
    {
        global $USER;
        if($this->studentId == $USER->id) return true;
        else return false;
    }

    private function get_not_available_page() : string 
    {
        $notAvailable = new ThemeSelectionNotAvailablePage;
        return $notAvailable->get_page();
    }

    private function get_theme_selection_page() : string 
    {
        $themeSelection = new ThemeSelectionPage($this->course, $this->cm, $this->studentId);
        return $themeSelection->get_page();
    }

    private function is_task_used_in_coursework_instance() : bool 
    {
        global $DB;
        $where = array('id'=>$this->cm->instance, 'usetask'=>1);
        return $DB->record_exists('coursework', $where);
    }






}
