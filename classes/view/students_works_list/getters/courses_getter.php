<?php

namespace Coursework\View\StudentsWorksList;

use Coursework\View\StudentsWorksList\CoursesSelector as cs;
use Coursework\View\StudentsWorksList\MainGetter as mg;
use Coursework\Lib\Getters\CoursesGetter as coug;
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

    public function add_courses_from_student_works($students)
    {
        foreach($students as $student)
        {
            if(!empty($student->course))
            {
                if($this->is_course_not_exist_in_course_array($student->course))
                {
                    $newCourse = new \stdClass;
                    $newCourse->id = $student->course;
                    $newCourse->fullname = coug::get_course_fullname($newCourse->id);
    
                    $this->courses[] = $newCourse;
                }
            }
        }

        $this->sort_courses();

        return $this->courses;
    }

    private function is_course_not_exist_in_course_array($courseId) : bool 
    {
        foreach($this->courses as $course)
        {
            if($course->id == $courseId)
            {
                return false;
            }
        }

        return true;
    }

    private function sort_courses()
    {
        $firstItem = array($this->courses[0]);

        unset($this->courses[0]);

        usort($this->courses, function($a, $b)
        {
            return strcmp($a->fullname, $b->fullname);
        });

        $this->courses = array_merge($firstItem, $this->courses);
    }

    private function merge_courses($tCourses, $sCourses)
    {
        foreach($sCourses as $sCourse)
        {
            if($this->is_course_unique($tCourses, $sCourse))
            {
                $tCourses[] = $sCourse;
            }
        }

        usort($tCourses, function($a, $b)
        {
            return strcmp($a->fullname, $b->fullname);
        });

        return $tCourses;
    }

    private function is_course_unique($uniques, $course) : bool 
    {
        foreach($uniques as $unique)
        {
            if($unique->id == $course->id)
            {
                return false;
            }
        }

        return true;
    }

    private function init_courses()
    {
        if($this->selectedTeacherId == mg::ALL_COURSES)
        {
            $courses = $this->get_courses_from_coursework_teachers();
        }
        else 
        {
            $courses = $this->get_teacher_courses();
        }

        $courses = $this->add_all_courses_item_to_courses($courses);

        $this->courses = $courses;
    }

    private function get_courses_from_coursework_teachers()
    {
        $courses = coug::get_coursework_teachers_courses($this->cm->instance);
        $courses = $this->courses_unique_from_coursework_teachers($courses);

        return $courses;
    }

    private function courses_unique_from_coursework_teachers($courses)
    {
        $unique = array();

        foreach($courses as $course)
        {
            if($this->is_course_unique($unique, $course))
            {
                $unique[] = $course;
            }
        }

        return $unique;
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
        else
        {
            $this->selectedCourseId = $course;
        }
    }


}
