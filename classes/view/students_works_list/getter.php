<?php

namespace View\StudentsWorksList;

use CourseWork\LocalLib as lib;

class Getter 
{
    private $course;
    private $cm;

    function __construct(\stdClass $course, \stdClass $cm) 
    {
        $this->course = $course;
        $this->cm = $cm;


    }

    public function get_course() : \stdClass
    {
        return $this->course;
    }

    public function get_cm() : \stdClass
    {
        return $this->cm;
    }

    public function get_course_work_name() : string 
    {
        return lib::get_coursework_name($this->cm->instance);
    }

    public function get_group_mode() 
    {
        return lib::get_coursework_group_mode($this->cm);
    }

    public function get_groups() 
    {
        return lib::get_coursework_groups($this->cm);
    }







}