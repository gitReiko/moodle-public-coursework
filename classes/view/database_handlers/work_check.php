<?php

use coursework_lib as lib;
use view_lib as view;

class WorkCheckDatabaseHandler 
{
    private $course;
    private $cm;

    private $work;

    function __construct(stdClass $course, stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->work = $this->get_work();
    }

    public function handle()
    {
        $this->update_work_status();
    }


    private function get_work() : stdClass 
    {
        $student = $this->get_student();
        $work = lib\get_student_work($this->cm, $student);
        $work->status = $this->get_status();

        if($work->status == READY)
        {
            $work->grade = $this->get_grade();
        }

        $work->workstatuschangedate = time();
        return $work;
    }

    private function get_student() : int 
    {
        $student = optional_param(STUDENT, null, PARAM_INT);
        if(empty($student)) throw new Exception('Missing student id.');
        return $student;
    }

    private function get_status() : string 
    {
        $status = optional_param(STATUS, null, PARAM_TEXT);
        if(empty($status)) throw new Exception('Missing work status.');
        return $status;
    }

    private function get_grade() : int 
    {
        $grade = optional_param(GRADE, null, PARAM_INT);
        if(empty($grade)) throw new Exception('Missing work grade.');
        return $grade;
    }

    private function update_work_status()
    {
        if($this->work->status == READY)
        {
            $this->save_grade_in_gradebook();

            if(view\is_coursework_use_task($this->cm))
            {
                $this->update_user_task_sections_to_ready();
            }
        }

        global $DB;
        return $DB->update_record('coursework_students', $this->work);
    }

    private function save_grade_in_gradebook() : void 
    {
        $grade = new stdClass;
        $grade->userid   = $this->work->student;
        $grade->rawgrade = $this->work->grade;
        $coursework = lib\get_coursework($this->cm->instance);
        coursework_grade_item_update($coursework, $grade);
    }

    private function update_user_task_sections_to_ready() : void 
    {
        $sections = lib\get_sections_to_check($this->cm, $this->work->student);

        foreach($sections as $section)
        {
            $sectionRow = $this->get_section($section->id);
            if($this->is_student_task_section_exist($section->id))
            {
                if($this->is_section_status_not_ready($section->id))
                {
                    $sectionRow->id = $this->get_section_id($section->id);
                    $this->update_section_in_database($sectionRow);
                }
            }
            else
            {
                $this->insert_section_to_database($sectionRow);
            }
        }
    }

    private function is_student_task_section_exist(int $sectionId) : bool 
    {
        global $DB;
        $conditions = array('coursework' => $this->cm->instance,
                            'student' => $this->work->student,
                            'section' => $sectionId);
        return $DB->record_exists('coursework_sections_status', $conditions);
    }

    private function is_section_status_not_ready(int $sectionId) : bool 
    {
        global $DB;
        $sql = 'SELECT * 
                FROM {coursework_sections_status}
                WHERE coursework = ?
                AND student = ? 
                AND section = ? 
                AND status != ?';  
        $params = array($this->cm->instance, $this->work->student, $sectionId, READY);
        return $DB->record_exists_sql($sql, $params);
    }

    private function get_section(int $sectionId) : stdClass 
    {
        $section = new stdClass;
        $section->coursework = $this->cm->instance;
        $section->student = $this->work->student;
        $section->section = $sectionId;
        $section->status = READY;
        $section->timemodified = time();
        return $section;
    }

    private function insert_section_to_database(stdClass $section) : void 
    {
        global $DB;
        $DB->insert_record('coursework_sections_status', $section);
    }

    private function get_section_id(int $sectionId) : int 
    {
        global $DB;
        $conditions = array('coursework' => $this->cm->instance,
                            'student' => $this->work->student,
                            'section' => $sectionId);
        return $DB->get_field('coursework_sections_status', 'id', $conditions);
    }
    
    private function update_section_in_database(stdClass $section) : void 
    {
        global $DB;
        $DB->update_record('coursework_sections_status', $section);
    }






}
