<?php

namespace Coursework\View\StudentWork\ThemeSelection;

use Coursework\Lib\Getters\StudentsGetter as sg;

require_once 'teachers_and_courses.php';
require_once 'themes_getter.php';

class MainGetter  
{
    private $course;
    private $cm;
    private $studentId;
    private $students;

    private $availableTeachers;
    private $availableCourses;
    private $availableThemes;
    private $selectedCourses;
    private $selectedThemes;
    private $selectedTeacher;
    private $selectedCourse;

    function __construct(\stdClass $course, \stdClass $cm, int $studentId)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->studentId = $studentId;
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

    public function get_selected_courses()
    {
        return $this->selectedCourses;
    }

    public function get_selected_themes()
    {
        return $this->selectedThemes;
    }

    public function get_selected_teacher() 
    {
        return $this->selectedTeacher;
    }

    public function get_selected_course()
    {
        return $this->selectedCourse;
    }

    private function init_available_teachers_and_courses() : void 
    {
        $getter = new TeachersAndCoursesGetter($this->course, $this->cm, $this->studentId, $this->students);
        $this->availableTeachers = $getter->get_available_teachers();
        $this->availableCourses = $getter->get_available_courses();
        $this->selectedCourses = $getter->get_selected_courses();
        $this->selectedTeacher = $getter->get_selected_teacher();
        $this->selectedCourse = $getter->get_selected_course();
    }

    private function init_available_themes() : void 
    {
        $getter = new ThemesGetter(
            $this->course, 
            $this->cm, 
            $this->availableCourses, 
            $this->students, 
            $this->selectedCourse
        );
        $this->availableThemes = $getter->get_available_themes();
        $this->selectedThemes = $getter->get_selected_themes();
    }



}
