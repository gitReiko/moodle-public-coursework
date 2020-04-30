<?php

require_once 'work_completion.php';
require_once 'student_work_completion.php';

use coursework_lib as lib;

class WorkCompletionMain 
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

        if(lib\is_user_student($this->cm, $USER->id))
        {
            return $this->get_student_page();
        }

        return 'vsvsvsv';
    }


    private function get_student_page() : string 
    {
        global $USER;
        $taskAssign = new StudentWorkComplition($this->course, $this->cm, $USER->id);
        return $taskAssign->get_page();
    }




}

