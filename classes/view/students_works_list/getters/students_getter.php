<?php

namespace Coursework\View\StudentsWorksList;

use Coursework\Lib\Getters\StudentsGetter as sg;
use Coursework\View\StudentsWorksList\GroupsSelector as grp;
use Coursework\Lib\Enums as enum;

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
        if($this->groupMode === enum::NO_GROUPS)
        {
            $this->students = sg::get_all_students($this->cm);
        }
        else if($this->selectedGroupId === grp::ALL_GROUPS)
        {
            $this->students = sg::get_students_from_available_groups($this->cm, $this->availableGroups);
        }
        else 
        {
            $this->students = sg::get_students_from_group($this->cm, $this->selectedGroupId);
        }
    }







}