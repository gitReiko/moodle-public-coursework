<?php

namespace View\StudentsWorksList;

use View\StudentsWorksList\GroupsSelector as grp;
use CourseWork\LocalLib as lib;

class StudentsGetter 
{
    private $course;
    private $cm;

    private $groupMode;
    private $selectedGroupId;
    private $availableGroups;

    private $students;

    function __construct(
        \stdClass $course, 
        \stdClass $cm,
        int $groupMode,
        int $selectedGroupId,
        $availableGroups
    ) 
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->groupMode = $groupMode;
        $this->selectedGroupId = $selectedGroupId;
        $this->availableGroups = $availableGroups;
        $this->init_students();
    }

    public function get_students() 
    {
        return $this->students;
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