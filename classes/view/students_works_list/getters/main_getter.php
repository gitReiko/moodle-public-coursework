<?php

namespace Coursework\View\StudentsWorksList;

require_once 'groups_getter.php';
require_once 'students_getter.php';

use Coursework\View\StudentsWorksList\GroupsSelector as grp;
use Coursework\View\StudentsWorksList\TeachersSelector as ts;
use Coursework\Lib\Getters\TeachersGetter as tg;
use Coursework\Lib\Getters\CommonGetter as cg;

class MainGetter 
{
    private $course;
    private $cm;

    private $groupMode;
    private $selectedGroupId;
    private $availableGroups;
    private $groups;

    private $teachers;
    private $selectedTeacherId;

    private $students;

    function __construct(\stdClass $course, \stdClass $cm) 
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->init_group_params();
        $this->init_teachers();
        $this->init_selected_teacher();
        $this->init_students();
    }

    public function get_course() : \stdClass
    {
        return $this->course;
    }

    public function get_cm() : \stdClass
    {
        return $this->cm;
    }

    public function get_course_work_name() : string 
    {
        return cg::get_coursework_name($this->cm->instance);
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

    public function get_students() 
    {
        return $this->students;
    }

    private function init_group_params() 
    {
        $grp = new GroupsGetter($this->course, $this->cm);

        $this->groupMode = $grp->get_group_mode();
        $this->groups = $grp->get_groups();
        $this->selectedGroupId = $grp->get_selected_group_id();
        $this->availableGroups = $grp->get_available_groups();
    }

    private function init_teachers() 
    {
        $this->teachers = tg::get_all_course_work_teachers($this->cm->instance);
    }

    private function init_selected_teacher()
    {
        $teacher = optional_param(ts::TEACHER, null, PARAM_INT);

        if(empty($teacher))
        {
            $this->selectedTeacherId = reset($this->teachers)->teacherid;
        }
        else 
        {
            $this->selectedTeacherId = $teacher;
        }
    }

    private function init_students() 
    {
        $st = new StudentsGetter(
            $this->course, 
            $this->cm,
            $this->groupMode,
            $this->selectedGroupId,
            $this->availableGroups
        );

        $this->students = $st->get_students();
    }




}