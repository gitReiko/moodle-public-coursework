<?php

use coursework_lib as lib;

class SendWorkForCheckDatabaseHandler 
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
        $work->status = SENT_TO_CHECK;
        $work->workstatuschangedate = time();
        return $work;
    }

    private function get_student() : int 
    {
        $student = optional_param(STUDENT, null, PARAM_INT);
        if(empty($student)) throw new Exception('Missing student id.');
        return $student;
    }


    private function update_work_status()
    {
        global $DB;
        return $DB->update_record('coursework_students', $this->work);
    }


}
