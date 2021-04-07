<?php

namespace View\StudentsWorksList;

require_once 'groups_getter.php';
require_once 'students_getter.php';

use View\StudentsWorksList\GroupsSelector as grp;
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

    private $students;
    private $leaders;

    function __construct(\stdClass $course, \stdClass $cm) 
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->init_group_params();
        $this->init_leaders();
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

    public function get_leaders()
    {
        return $this->leaders;
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

    private function init_leaders() 
    {
        $this->leaders = tg::get_all_course_work_teachers($this->cm->instance);
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