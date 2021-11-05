<?php

namespace Coursework\View\StudentsWorksList;

use Coursework\View\StudentsWorksList\CoursesSelector as cs;
use Coursework\View\StudentsWorksList\MainGetter as mg;
use Coursework\Lib\Getters\TeachersGetter as tg;

class CoursesGetter 
{
    private $course;
    private $cm;

    private $courses;
    private $selectedCourseId;
    private $selectedTeacherId;

    function __construct(\stdClass $course, \stdClass $cm, int $selectedTeacherId) 
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->selectedTeacherId = $selectedTeacherId;

        $this->init_courses();
        $this->init_selected_course_id();
    }

    public function get_courses() 
    {
        return $this->courses;
    }

    public function get_selected_course_id()
    {
        return $this->selectedCourseId;
    }

    private function init_courses()
    {
        if($this->selectedTeacherId == mg::ALL_COURSES)
        {

        }
        else 
        {
            $courses = $this->get_teacher_courses();
        }

        $courses = $this->add_all_courses_item_to_courses($courses);

        $this->courses = $courses;
    }

    private function get_all_coursework_courses()
    {
        // from conf leaders
        // from students works
    }

    private function get_teacher_courses()
    {
        return tg::get_teacher_courses(
            $this->cm->instance, 
            $this->selectedTeacherId
        );
    }

    private function add_all_courses_item_to_courses($courses)
    {
        $allCourses = array($this->get_all_courses_item());
        return array_merge($allCourses, $courses);
    }

    private function get_all_courses_item() : \stdClass 
    {
        $allCourses = new \stdClass;
        $allCourses->id = mg::ALL_COURSES;
        $allCourses->fullname = get_string('all_courses', 'coursework');
        $allCourses->shortname = get_string('all_courses', 'coursework');

        return $allCourses;
    }

    private function init_selected_course_id()
    {
        $course = optional_param(cs::COURSE, null, PARAM_INT);

        if(empty($course))
        {
            $this->selectedCourseId = reset($this->courses)->id;
        }
        else if(tg::is_this_course_is_teacher_course($this->cm->instance, $this->selectedTeacherId, $course))
        {
            $this->selectedCourseId = $course;
        }
        else 
        {
            $this->selectedCourseId = reset($this->courses)->id;
        }
    }


}
