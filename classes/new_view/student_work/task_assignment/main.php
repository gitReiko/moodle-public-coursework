<?php

require_once 'task_issuance.php';

use coursework_lib as lib;

class TaskAssignmentMain 
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
        global $USER;
        if(lib\is_user_teacher($this->cm, $USER->id))
        {
            return $this->get_task_issuance_page();
        }
        else 
        {
            return 'student page';
        }
    }

    private function get_task_issuance_page() : string 
    {
        $issuance = new TaskIssuance($this->course, $this->cm, $this->studentId);
        return $issuance->get_page();
    }


}