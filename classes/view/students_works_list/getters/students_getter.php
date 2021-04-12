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

    private $teacherStudents;
    private $studentsWithoutTeacher;

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

    public function get_teacher_students() 
    {
        return $this->teacherStudents;
    }

    public function get_students_without_teacher() 
    {
        return $this->studentsWithoutTeacher;
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

        $this->teacherStudents = $this->get_teacher_students_from_array($students);
        $this->studentsWithoutTeacher = $this->get_students_without_teacher_from_array($students);
    }

    private function get_teacher_students_from_array($students)
    {
        $teacherStudents = array();
        foreach($students as $student) 
        {
            if($this->is_students_belong_to_teacher($student))
            {
                $teacherStudents[] = $student;
            }
        }
        return $teacherStudents;
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

    private function get_students_without_teacher_from_array($students) 
    {
        $withoutTeacher = array();
        foreach($students as $student) 
        {
            if($this->is_student_without_teacher($student))
            {
                $withoutTeacher[] = $student;
            }
        }
        return $withoutTeacher;
    }

    private function is_student_without_teacher($student) : bool 
    {
        if(empty($student->teacher))
        {
            return true;
        }
        else 
        {
            return false;
        }
    }









}