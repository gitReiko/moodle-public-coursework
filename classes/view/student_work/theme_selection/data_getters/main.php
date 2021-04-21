<?php

require_once 'leaders_and_courses_getter.php';
require_once 'themes_getter.php';

use coursework_lib as lib;

class ThemeSelectionMainGetter  
{
    private $course;
    private $cm;

    private $availableLeaders;
    private $availableCourses;
    private $availableThemes;

    function __construct(stdClass $course, stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->init_available_leaders_and_courses();
        $this->init_available_themes();
    }

    public function get_available_leaders()
    {
        return $this->availableLeaders;
    }

    public function get_available_courses() 
    {
        return $this->availableCourses;
    } 

    public function get_available_themes()
    {
        return $this->availableThemes;
    }

    public function get_selected_leader() 
    {
        return reset($this->availableLeaders);
    }

    public function get_selected_course()
    {
        return reset(reset($this->availableLeaders)->courses);
    }

    private function init_available_leaders_and_courses() : void 
    {
        $getter = new LeadersAndCoursesGetter($this->course, $this->cm);
        $this->availableLeaders = $getter->get_available_leaders();
        $this->availableCourses = $getter->get_available_courses();
    }

    private function init_available_themes() : void 
    {
        $getter = new ThemesGetter($this->course, $this->cm, $this->availableCourses);
        $this->availableThemes = $getter->get_available_themes();
    }



}
