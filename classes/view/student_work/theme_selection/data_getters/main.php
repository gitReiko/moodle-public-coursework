<?php

use Coursework\Lib\Getters\StudentsGetter as sg;

require_once 'teachers_and_courses.php';
require_once 'themes_getter.php';

class ThemeSelectionMainGetter  
{
    private $course;
    private $cm;
    private $students;

    private $availableTeachers;
    private $availableCourses;
    private $availableThemes;

    function __construct(stdClass $course, stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->students = sg::get_all_students($this->cm);

        $this->init_available_teachers_and_courses();
        $this->init_available_themes();
    }

    public function get_available_teachers()
    {
        return $this->availableTeachers;
    }

    public function get_available_courses() 
    {
        return $this->availableCourses;
    } 

    public function get_available_themes()
    {
        return $this->availableThemes;
    }

    public function get_selected_teacher() 
    {
        return reset($this->availableTeachers);
    }

    public function get_selected_course()
    {
        return reset(reset($this->availableTeachers)->courses);
    }

    private function init_available_teachers_and_courses() : void 
    {
        $getter = new TeachersAndCoursesGetter($this->course, $this->cm, $this->students);
        $this->availableTeachers = $getter->get_available_teachers();
        $this->availableCourses = $getter->get_available_courses();
    }

    private function init_available_themes() : void 
    {
        $getter = new ThemesGetter($this->course, $this->cm, $this->availableCourses, $this->students);
        $this->availableThemes = $getter->get_available_themes();
    }



}
