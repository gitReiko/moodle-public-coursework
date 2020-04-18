<?php

require_once 'theme_selection_stage\main.php';

use coursework_lib as lib;

class StudentWorkMain 
{
    const COURSEWORK_STAGE = 'coursework_stage';
    const THEME_SELECTION = 'theme_selection';
    const TASK_GETTING = 'task_getting';
    const WORK_COMPLETION = 'work_completion';

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
        if($this->is_theme_not_selected())
        {
            return $this->get_theme_selection_page();
        }
        // Other stages
    }

    private function get_theme_selection_page() : string 
    {
        $themeSelection = new ThemeSelectionStageMain($this->course, $this->cm, $this->studentId);
        return $themeSelection->get_gui();
    }

    private function is_theme_not_selected() : bool 
    {
        global $DB;
        $conditions = array('coursework' => $this->cm->instance, 'student' => $this->studentId);
        return !$DB->record_exists('coursework_students', $conditions);
    }


}

