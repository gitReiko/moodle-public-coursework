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
        $availableGroups,
        $selectedTeacherId,
        $selectedCourseId
    ) 
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->groupMode = $groupMode;
        $this->selectedGroupId = $selectedGroupId;
        $this->availableGroups = $availableGroups;
        $this->selectedTeacherId = $selectedTeacherId;
        $this->selectedCourseId = $selectedCourseId;
        $this->init_students();
    }

    public function get_students() 
    {
        return $this->students;
    }

    private function init_students() 
    {
        $students = array();

        if($this->groupMode === enum::NO_GROUPS)
        {
            $students = sg::get_all_students($this->cm);
        }
        else if($this->selectedGroupId === grp::ALL_GROUPS)
        {
            $students = sg::get_students_from_available_groups($this->cm, $this->availableGroups);
        }
        else 
        {
            $students = sg::get_students_from_group($this->cm, $this->selectedGroupId);
        }

        $students = sg::add_works_to_students($this->cm->instance, $students);
        $students = $this->filter_out_non_teacher_students($students);

        $this->students = $students;
    }

    private function filter_out_non_teacher_students($students)
    {
        $filteredStudents = array();

        foreach($students as $student) 
        {
            if($this->is_students_belong_to_teacher($student))
            {
                $filteredStudents[] = $student;
            }
        }

        return $filteredStudents;
    }

    private function is_students_belong_to_teacher($student) : bool 
    {
        if
        (
            ($student->teacher == $this->selectedTeacherId)
            && 
            ($student->course == $this->selectedCourseId)
        )
        {
            return true;
        }
        else 
        {
            return false;
        }
    }







}