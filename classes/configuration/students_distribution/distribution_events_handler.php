<?php

class StudentsDistributionDBEventsHandler 
{
    private $course;
    private $cm;

    function __construct(stdClass $course, stdClass $cm) 
    {
        $this->course = $course;
        $this->cm = $cm;
    }

    public function execute() 
    {
        echo "work";
    }


}

