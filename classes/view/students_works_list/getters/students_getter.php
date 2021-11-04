<?php

namespace Coursework\View\StudentsWorksList;

use Coursework\View\StudentsWorksList\GroupsSelector as grp;
use Coursework\View\StudentsWorksList\MainGetter as mg;
use Coursework\Lib\Getters\StudentsGetter as sg;
use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\Getters\StudentTaskGetter;
use Coursework\Lib\Enums as enum;

class StudentsGetter 
{
    private $course;
    private $cm;
    private $coursework;

    private $groupMode;
    private $selectedGroupId;

    private $students;

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
            $students = sg::get_students_from_available_groups($this->cm);
        }
        else 
        {
            $students = sg::get_students_from_group($this->cm, $this->selectedGroupId);
        }

        $students = sg::add_works_to_students($this->cm->instance, $students);

        if($this->is_selected_course_is_not_all_courses())
        {
            $students = $this->filter_out_all_unnecessary_courses($students);
        }

        if($this->is_selected_teacher_is_not_all_teachers())
        {
            $students = $this->filter_out_all_unnecessary_teachers($students);
        }

        if($this->coursework->usetask == 1)
        {
            $students = $this->add_students_task_sections($students);
        }

        $this->students = $students;
    }

    private function is_selected_course_is_not_all_courses() : bool 
    {
        if($this->selectedCourseId == mg::ALL_COURSES)
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    private function is_selected_teacher_is_not_all_teachers() : bool 
    {
        if($this->selectedTeacherId == mg::ALL_TEACHERS)
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    private function filter_out_all_unnecessary_courses($students)
    {
        $filtered = array();

        foreach($students as $student)
        {
            if($student->course == $this->selectedCourseId)
            {
                $filtered[] = $student;
            }
        }

        return $filtered;
    }

    private function filter_out_all_unnecessary_teachers($students)
    {
        $filtered = array();

        foreach($students as $student)
        {
            if($student->teacher == $this->selectedTeacherId)
            {
                $filtered[] = $student;
            }
        }

        return $filtered;
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