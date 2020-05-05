<?php

use coursework_lib as lib;

class StudentWorkComplition extends WorkCompletion
{
    protected $course;
    protected $cm;
    protected $studentId;

    function __construct(stdClass $course, stdClass $cm, int $studentId)
    {
        parent::__construct($course, $cm, $studentId);


    }

    protected function get_additional_modules() : string
    {
        return $this->get_send_for_check_module();
    }

    private function get_send_for_check_module() : string 
    {
        $studentActions = new SendForCheck($this->course, $this->cm, $this->studentId, true);
        return $studentActions->get_module();
    }



}

