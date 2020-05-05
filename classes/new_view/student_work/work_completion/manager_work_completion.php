<?php

use coursework_lib as lib;

class ManagerWorkComplition extends WorkCompletion
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
        return '';
    }





}

