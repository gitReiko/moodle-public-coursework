<?php

namespace Coursework\View\StudentWork\ThemeSelection;

use Coursework\Lib\Getters\StudentsGetter as sg;
use Coursework\Lib\Getters\TeachersGetter as tg;

class TeachersAndCoursesGetter  
{
    private $course;
    private $cm;
    private $studentId;
    private $students;
    private $teachers;
    private $studentWork;
 
    private $availableTeachers;
    private $availableCourses;
    private $selectedCourses;

    function __construct(\stdClass $course, \stdClass $cm, int $studentId, $students)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->studentId = $studentId;
        $this->students = $students;
        $this->studentWork = sg::get_students_work($this->cm->instance, $this->studentId);
        $this->teachers = $this->init_teachers();
        $this->availableTeachers = $this->init_available_teachers();
        $this->selectedCourses = $this->init_selected_courses();
        $this->availableCourses = $this->init_available_courses();
    }

    public function get_available_teachers()
    {
        return $this->availableTeachers;
    }

    public function get_available_courses()
    {
        return $this->availableCourses;
    }

    public function get_selected_courses()
    {
        return $this->selectedCourses;
    }

    public function get_selected_teacher() 
    {
        return reset($this->availableTeachers);
    }

    public function get_selected_course()
    {
        return reset($this->availableCourses);
    }

    private function init_teachers() 
    {
        $teachers = tg::get_coursework_teachers($this->cm->instance);
        $teachers = $this->add_courses_with_quotas_to_teachers($teachers);

        if($this->is_student_selected_teacher())
        {
            $teachers = $this->filter_out_unselected_teachers($teachers);
        }

        return $teachers;
    }

    private function add_courses_with_quotas_to_teachers($teachers)
    {
        foreach($teachers as $teacher)
        {
            $teacher->courses = tg::get_teacher_courses($this->cm->instance, $teacher->id);
            $teacher->courses = tg::get_courses_with_quotas($this->cm, $teacher->id, $teacher->courses);
        }

        return $teachers;
    }

    private function is_student_selected_teacher() : bool 
    {
        if(empty($this->studentWork->teacher))
        {
            return false;
        }
        else 
        {
            return true;
        }
    }

    private function filter_out_unselected_teachers($teachers)
    {
        foreach($teachers as $teacher)
        {
            if($this->studentWork->teacher == $teacher->id)
            {
                return array($teacher);
            }
        }
    }

    private function init_available_teachers()
    {
        $availableTeachers = array();

        foreach($this->teachers as $teacher)
        {
            if($this->is_teacher_quota_is_not_exhausted($teacher)
                || $this->is_teacher_selected_by_student($teacher))
            {
                $tempTeacher = new \stdClass;
                $tempTeacher->id = $teacher->id;
                $tempTeacher->name = $teacher->lastname.' '.$teacher->firstname;
                $tempTeacher->courses = $teacher->courses;

                $availableTeachers[] = $tempTeacher;
            }
        }

        return $availableTeachers;
    }

    private function is_teacher_selected_by_student($teacher) : bool 
    {
        if($this->studentWork->teacher == $teacher->id)
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    private function is_teacher_quota_is_not_exhausted($teacher)
    {
        foreach($teacher->courses as $course)
        {
            if($course->available_quota > 0)
            {
                return true;
            }
        }

        return false;
    } 

    private function init_available_courses()
    {
        $availableCourses = array();

        foreach($this->teachers as $teacher)
        {
            foreach($teacher->courses as $course)
            {
                if(($course->available_quota > 0)
                    || $this->is_course_selected_by_student($course))
                {
                    $tempCourse = new \stdClass;
                    $tempCourse->id = $course->id;
                    $tempCourse->name = $course->fullname;
    
                    $availableCourses[] = $tempCourse;
                }
            }
        }

        return $availableCourses;
    }

    private function is_course_selected_by_student($course) : bool 
    {
        if($this->studentWork->course == $course->id)
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    private function filter_out_unselected_courses($courses)
    {
        foreach($courses as $course)
        {
            if($this->studentWork->course == $course->id)
            {
                return array($course);
            }
        }
    }

    private function init_selected_courses()
    {
        $firstTeacher = reset($this->availableTeachers);

        $availableCourses = array();
        foreach($firstTeacher->courses as $course)
        {
            if(($course->available_quota > 0)
                || $this->is_course_selected_by_student($course))
            {
                $tempCourse = new \stdClass;
                $tempCourse->id = $course->id;
                $tempCourse->name = $course->fullname;

                $availableCourses[] = $tempCourse;
            }
        }

        return $availableCourses;
    }




}
