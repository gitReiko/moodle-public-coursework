<?php

namespace Coursework\View\StudentsWorksList;

use Coursework\View\StudentsWorksList\StudentsHider as sh;

use Coursework\View\StudentsWorksList\StudentsNamesFilter as snf;

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

    private $lastnameFilter;
    private $firstnameFilter;

    private $students;

    private $hideStudents;

    function __construct(\stdClass $course, \stdClass $cm) 
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->init_hide_students_without_theme();
        
        $groupsGtr = $this->get_groups_getter();
        $this->init_group_params($groupsGtr);

        $teachersGtr = $this->get_teachers_getter();
        $this->init_teachers_params($teachersGtr);

        $coursesGtr = $this->get_courses_getter();
        $this->init_courses_params($coursesGtr);

        $this->lastnameFilter = $this->init_lastname_filter();
        $this->firstnameFilter = $this->init_firstname_filter();

        $studentsGtr = $this->get_students_getter();
        $this->init_students($studentsGtr);

        $this->add_student_courses_to_courses($coursesGtr);
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

    public function get_lastname_filter()
    {
        return $this->lastnameFilter;
    }
    
    public function get_firstname_filter()
    {
        return $this->firstnameFilter;
    }

    public function get_students() 
    {
        return $this->students;
    }

    public function is_hide_students_without_theme()
    {
        return $this->hideStudentsWithoutTheme;
    }

    private function get_groups_getter()
    {
        return new GroupsGetter($this->course, $this->cm);
    }

    private function init_group_params($groupGetter) 
    {
        $this->groupMode = $groupGetter->get_group_mode();
        $this->groups = $groupGetter->get_groups();
        $this->selectedGroupId = $groupGetter->get_selected_group_id();
        $this->availableGroups = $groupGetter->get_available_groups();
    }

    private function get_teachers_getter()
    {
        return new TeachersGetter($this->course, $this->cm);
    }

    private function init_teachers_params($teacherGetter) 
    {
        $this->teachers = $teacherGetter->get_teachers();
        $this->selectedTeacherId = $teacherGetter->get_selected_teacher_id();
    }

    private function get_courses_getter()
    {
        return new CoursesGetter(
            $this->course, 
            $this->cm, 
            $this->selectedTeacherId
        );
    }

    private function init_courses_params($coursesGtr)
    {
        $this->courses = $coursesGtr->get_courses();
        $this->selectedCourseId = $coursesGtr->get_selected_course_id();
    }

    private function init_lastname_filter()
    {
        return optional_param(snf::LASTNAME, snf::ALL, PARAM_TEXT);
    }

    private function init_firstname_filter()
    {
        return optional_param(snf::FIRSTNAME, snf::ALL, PARAM_TEXT);
    }

    private function get_students_getter()
    {
        return new StudentsGetter(
            $this->course, 
            $this->cm,
            $this->groupMode,
            $this->selectedGroupId,
            $this->selectedTeacherId,
            $this->selectedCourseId,
            $this->hideStudentsWithoutTheme
        );
    }

    private function init_students($studentsGtr) 
    {
        $this->students = $studentsGtr->get_students();
    }

    private function filter_out_not_student_teachers($teachersGtr, $students)
    {
        $this->teachers = $teachersGtr->filter_out_not_student_teachers($students);
    }

    private function add_student_courses_to_courses($coursesGtr)
    {
        $this->courses = $coursesGtr->add_courses_from_student_works($this->students);
    }

    private function init_hide_students_without_theme()
    {
        $hider = optional_param(sh::HIDE_STUDENTS_WITHOUT_THEME, null, PARAM_INT);

        if($hider)
        {
            $this->hideStudentsWithoutTheme = true;
        }
        else 
        {
            $this->hideStudentsWithoutTheme = false;
        }
    }



}