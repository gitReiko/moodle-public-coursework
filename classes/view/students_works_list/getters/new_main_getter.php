<?php

namespace Coursework\View\StudentsWorksList;

require_once 'groups_getter.php';
require_once 'teachers_getter.php';

/*
require_once 'students_getter.php';

use Coursework\View\StudentsWorksList\GroupsSelector as grp;
use Coursework\View\StudentsWorksList\TeachersSelector as ts;
use Coursework\View\StudentsWorksList\CoursesSelector as cs;

use Coursework\Lib\Getters\CommonGetter as cg;
*/

class NewMainGetter 
{
    const ALL_TEACHERS = -1;

    private $course;
    private $cm;
    
    private $groupMode;
    private $selectedGroupId;
    private $availableGroups;
    private $groups;

    private $teachers;
    private $selectedTeacherId;

    /*

    private $courses;
    private $selectedCourseId;

    private $teacherStudents;
    private $studentsWithoutTeacher;
    */

    function __construct(\stdClass $course, \stdClass $cm) 
    {
        $this->course = $course;
        $this->cm = $cm;
        
        $this->init_group_params();
        $this->init_teachers_params();
/*
        if($this->is_teachers_exists())
        {
            $this->init_selected_teacher();
            $this->init_courses();
            $this->init_selected_course_id();
            $this->init_students();
        }
        */
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

    public function get_teachers()
    {
        return $this->teachers;
    }

    public function get_selected_teacher_id()
    {
        return $this->selectedTeacherId;
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





}