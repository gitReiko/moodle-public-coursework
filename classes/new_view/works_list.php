<?php

use coursework_lib as lib;

class WorksList 
{
    private $course;
    private $cm;

    function __construct(stdClass $course, stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;
    }

    public function get_gui() : string 
    {
        return 'works list';
    }



}