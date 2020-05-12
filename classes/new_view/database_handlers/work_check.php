<?php

use coursework_lib as lib;

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
        global $DB;
        return $DB->update_record('coursework_students', $this->work);
    }


}
