<?php

namespace Coursework\View\StudentsWorksList;

use Coursework\View\StudentsWorksList\StudentsHider as sh;

require_once 'groups_getter.php';
require_once 'teachers_getter.php';
require_once 'courses_getter.php';
require_once 'students_getter.php';

class MainGetter 
{
    const ALL_TEACHERS = -1;
    const ALL_COURSES = -1;

    private $course;
    private $cm;
    
    private $groupMode;
    private $groups;
    private $availableGroups;
    private $selectedGroupId;

    private $teachers;
    private $selectedTeacherId;

    private $courses;
    private $selectedCourseId;

    private $students;

    private $hideStudents;

    function __construct(\stdClass $course, \stdClass $cm) 
    {
        $this->course = $course;
        $this->cm = $cm;
        
        $this->init_group_params();
        $this->init_teachers_params();
        $this->init_courses_params();
        $this->init_students();
        $this->add_student_courses();
        $this->init_hide_students();
    }

    public function get_course() : \stdClass
    {
        return $this->course;
    }

    public function get_cm() : \stdClass
    {
        return $this->cm;
    }

    public function get_group_mode() 
    {
        return $this->groupMode;
    }

    public function get_groups() 
    {
        return $this->groups;
    }

    public function get_selected_group_id() 
    {
        return $this->selectedGroupId;
    }

    public function get_teachers()
    {
        return $this->teachers;
    }

    public function get_selected_teacher_id()
    {
        return $this->selectedTeacherId;
    }

    public function get_courses() 
    {
        return $this->courses;
    }

    public function get_selected_course_id()
    {
        return $this->selectedCourseId;
    }

    public function get_students() 
    {
        return $this->students;
    }

    public function is_hide_students_without_theme()
    {
        return $this->hideStudents;
    }

    private function init_group_params() 
    {
        $grp = new GroupsGetter($this->course, $this->cm);

        $this->groupMode = $grp->get_group_mode();
        $this->groups = $grp->get_groups();
        $this->selectedGroupId = $grp->get_selected_group_id();
        $this->availableGroups = $grp->get_available_groups();
    }

    private function init_teachers_params() 
    {
        $teachGetter = new TeachersGetter($this->course, $this->cm);

        $this->teachers = $teachGetter->get_teachers();
        $this->selectedTeacherId = $teachGetter->get_selected_teacher_id();
    }

    private function init_courses_params()
    {
        $courseGetter = new CoursesGetter(
            $this->course, 
            $this->cm, 
            $this->selectedTeacherId
        );

        $this->courses = $courseGetter->get_courses();
        $this->selectedCourseId = $courseGetter->get_selected_course_id();
    }

    private function init_students() 
    {
        $st = new StudentsGetter(
            $this->course, 
            $this->cm,
            $this->groupMode,
            $this->selectedGroupId,
            $this->selectedTeacherId,
            $this->selectedCourseId
        );

        $this->students = $st->get_students();
    }

    private function add_student_courses()
    {
        $courseGetter = new CoursesGetter(
            $this->course, 
            $this->cm, 
            $this->selectedTeacherId
        );
        $courseGetter->add_student_courses($this->students);
        $this->courses = $courseGetter->get_courses();
    }

    private function init_hide_students()
    {
        $hider = optional_param(sh::HIDE_STUDENTS, null, PARAM_INT);

        if($hider)
        {
            $this->hideStudents = true;
        }
        else 
        {
            $this->hideStudents = false;
        }
    }



}