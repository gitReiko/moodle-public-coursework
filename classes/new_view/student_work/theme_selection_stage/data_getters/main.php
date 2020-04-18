<?php

require_once 'leaders_and_courses_getter.php';

use coursework_lib as lib;

class ThemeSelectionMainGetter  
{
    private $course;
    private $cm;

    private $availableLeaders;
    private $availableCourses;

    function __construct(stdClass $course, stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->init_available_leaders_and_courses();
    }

    public function get_available_leaders()
    {
        return $this->availableLeaders;
    }

    public function get_available_courses() 
    {
        return $this->availableCourses;
    } 

    private function init_available_leaders_and_courses() : void 
    {
        $getter = new LeadersAndCoursesGetter($this->course, $this->cm);

        $this->availableLeaders = $getter->get_available_leaders();
        $this->availableCourses = $getter->get_available_courses();
    }



}
