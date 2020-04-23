<?php

require_once 'not_available_page.php';
require_once 'theme_selection_page.php';

use coursework_lib as lib;

class ThemeSelectionMain 
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

    public function get_page() : string 
    {
        if($this->is_observer_student())
        {
            return $this->get_theme_selection_page();
        }
        else
        {
            return $this->get_not_available_page();
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
        $notAvailable = new ThemeSelectionNotAvailablePage($this->course, $this->cm);
        return $notAvailable->get_page();
    }

    private function get_theme_selection_page() : string 
    {
        $themeSelection = new ThemeSelectionPage($this->course, $this->cm, $this->studentId);
        return $themeSelection->get_page();
    }


}
