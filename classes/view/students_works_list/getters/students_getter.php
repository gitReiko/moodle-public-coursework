<?php

namespace Coursework\View\StudentsWorksList;

use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\Getters\StudentsGetter as sg;
use Coursework\View\StudentsWorksList\GroupsSelector as grp;
use Coursework\Lib\Getters\StudentTaskGetter;
use Coursework\Lib\Enums as enum;

class StudentsGetter 
{
    private $course;
    private $cm;
    private $coursework;

    private $groupMode;
    private $selectedGroupId;

    private $teacherStudents;
    private $studentsWithoutTeacher;

    function __construct(
        \stdClass $course, 
        \stdClass $cm,
        int $groupMode,
        int $selectedGroupId,
        $selectedTeacherId,
        $selectedCourseId
    ) 
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->coursework = cg::get_coursework($cm->instance);
        $this->groupMode = $groupMode;
        $this->selectedGroupId = $selectedGroupId;
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
            $students = sg::get_students_from_available_groups($this->cm);
        }
        else 
        {
            $students = sg::get_students_from_group($this->cm, $this->selectedGroupId);
        }

        $students = sg::add_works_to_students($this->cm->instance, $students);

        $this->studentsWithoutTeacher = $this->get_students_without_teacher_from_array($students);

        $teacherStudents = $this->get_teacher_students_from_array($students);

        if($this->coursework->usetask == 1)
        {
            $teacherStudents = $this->add_students_task_sections($teacherStudents);
        }

        $this->teacherStudents = $teacherStudents;
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

    private function add_students_task_sections($students)
    {
        foreach ($students as $student) 
        {
            $getter = new StudentTaskGetter(
                $this->cm->instance,
                $student->id
            );

            $student->sections = $getter->get_sections();
        }

        return $students;
    }








}