<?php

namespace View\StudentsWorksList;

require_once 'groups_getter.php';

use View\StudentsWorksList\GroupsSelector as grp;
use CourseWork\LocalLib as lib;

class MainGetter 
{
    private $course;
    private $cm;

    private $groupMode;
    private $selectedGroupId;
    private $availableGroups;
    private $groups;


    private $students;

    function __construct(\stdClass $course, \stdClass $cm) 
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->init_group_params();

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
        return lib::get_coursework_name($this->cm->instance);
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

    private function init_students() 
    {
        if($this->groupMode === lib::NO_GROUPS)
        {
            $this->students = lib::get_all_students($this->cm);
        }
        else if($this->selectedGroupId === grp::ALL_GROUPS)
        {
            $this->students = lib::get_students_from_available_groups($this->cm, $this->availableGroups);
        }
        else 
        {
            $this->students = lib::get_students_from_group($this->cm, $this->selectedGroupId);
        }
    }



}