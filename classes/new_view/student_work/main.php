<?php

require_once 'theme_selection\main.php';
require_once 'task_assignment\main.php';

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

    public function get_page() : string 
    {
        if($this->is_theme_not_selected())
        {
            return $this->get_theme_selection_page();
        }
        else if($this->is_coursework_use_task()
                    && $this->is_task_not_assign_to_student())
        {
            return $this->get_task_assignment_page();
        }
        // Other stages
    }

    private function get_theme_selection_page() : string 
    {
        $themeSelection = new ThemeSelectionMain($this->course, $this->cm, $this->studentId);
        return $themeSelection->get_page();
    }

    private function is_theme_not_selected() : bool 
    {
        global $DB;
        $conditions = array('coursework' => $this->cm->instance, 'student' => $this->studentId);
        $record = $DB->get_record('coursework_students', $conditions);

        if(!empty($record->theme) || !empty($record->owntheme))
        {
            return false;
        }
        else 
        {
            return true;
        }
    }

    private function is_coursework_use_task() : bool 
    {
        global $DB;
        $where = array('id'=>$this->cm->instance, 'usetask'=>1);
        return $DB->record_exists('coursework', $where);
    }

    private function is_task_not_assign_to_student() : bool 
    {
        global $DB;
        $sql = 'SELECT id
                FROM {coursework_students} 
                WHERE coursework = ? 
                AND student = ? 
                AND task IS NOT NULL';
        $params = array($this->cm->instance, $this->studentId);
        return $DB->record_exists_sql($sql, $params);
    }

    private function get_task_assignment_page() : string 
    {
        $taskAssign = new TaskAssignmentMain($this->course, $this->cm, $this->studentId);
        return $taskAssign->get_page();
    }




}

