<?php

use Coursework\Lib\Getters\TeachersGetter as tg;

class TeachersAndCoursesGetter  
{
    private $course;
    private $cm;

    private $students;
    private $teachers;
 
    private $availableTeachers;
    private $availableCourses;
    private $selectedCourses;

    function __construct(stdClass $course, stdClass $cm, $students)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->students = $students;

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

    private function init_available_teachers()
    {
        $availableTeachers = array();

        foreach($this->teachers as $teacher)
        {
            if($this->is_teacher_quota_is_not_exhausted($teacher))
            {
                $tempTeacher = new \stdClass;
                $tempTeacher->id = $teacher->id;
                $tempTeacher->name = $teacher->lastname.' '.$teacher->firstname;
                $tempTeacher->courses = $this->get_teachers_courses($teacher);

                $availableTeachers[] = $tempTeacher;
            }
        }

        return $availableTeachers;
    }

    private function get_teachers_courses(\stdClass $teacher)
    {
        $courses = array();
        foreach($teacher->courses as $course)
        {
            if($course->available_quota > 0)
            {
                $courses[] = $course->id;
            }
        }

        return $courses;
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

        return $false;
    } 

    private function init_available_courses()
    {
        $availableCourses = array();

        foreach($this->teachers as $teacher)
        {
            foreach($teacher->courses as $course)
            {
                if($course->available_quota > 0)
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

    private function init_selected_courses()
    {
        $firstTeacher = reset($this->teachers);

        $availableCourses = array();
        foreach($firstTeacher->courses as $course)
        {
            if($course->available_quota > 0)
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
