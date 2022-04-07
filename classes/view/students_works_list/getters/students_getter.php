<?php

namespace Coursework\View\StudentsWorksList;

use Coursework\View\StudentsWorksList\StudentsNamesFilter\Main as snf;
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

    private $lastnameFilter;
    private $firstnameFilter;

    private $students;
    private $studentsLetters;

    private $hideStudentsWithoutTheme;

    function __construct(
        \stdClass $course, 
        \stdClass $cm,
        int $groupMode,
        int $selectedGroupId,
        $selectedTeacherId,
        $selectedCourseId,
        $hideStudentsWithoutTheme,
        $lastnameFilter,
        $firstnameFilter
    ) 
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->coursework = cg::get_coursework($cm->instance);
        $this->groupMode = $groupMode;
        $this->selectedGroupId = $selectedGroupId;
        $this->selectedTeacherId = $selectedTeacherId;
        $this->selectedCourseId = $selectedCourseId;
        $this->hideStudentsWithoutTheme = $hideStudentsWithoutTheme;
        $this->lastnameFilter = $lastnameFilter;
        $this->firstnameFilter = $firstnameFilter;
        $this->init_students();
    }

    public function get_students() 
    {
        return $this->students;
    }

    public function get_students_letters()
    {
        return $this->studentsLetters;
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

        $students = sg::get_students_with_their_works($this->cm->instance, $students);

        if($this->hideStudentsWithoutTheme)
        {
            $students = $this->filter_out_all_students_without_theme($students);
        }

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

        $this->studentsLetters = $students;

        if($this->is_lastname_filter_exists())
        {
            $students = $this->filter_by_lastname($students);
        }

        if($this->is_firstname_filter_exists())
        {
            $students = $this->filter_by_firstname($students);
        }

        $this->students = $students;
    }

    private function filter_out_all_students_without_theme($students)
    {
        $filtered = array();

        foreach($students as $student)
        {
            if(!empty($student->theme))
            {
                $filtered[] = $student;
            }
        }

        return $filtered;
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

    private function is_lastname_filter_exists() : bool 
    {
        if(empty($this->lastnameFilter))
        {
            return false;
        }
        else 
        {
            return true;
        }
    }

    private function filter_by_lastname($students)
    {
        if($this->lastnameFilter == snf::ALL)
        {
            return $students;
        }
        else 
        {
            $students = $this->filter_by_lastname_letter($students);
        }

        return $students;
    }

    private function filter_by_lastname_letter($students)
    {
        $filtered = array();

        foreach($students as $student)
        {
            $firstLetter = mb_substr($student->lastname, 0, 1);

            if($firstLetter == $this->lastnameFilter)
            {
                $filtered[] = $student;
            }
        }

        return $filtered;
    }

    private function is_firstname_filter_exists() : bool 
    {
        if(empty($this->firstnameFilter))
        {
            return false;
        }
        else 
        {
            return true;
        }
    }

    private function filter_by_firstname($students)
    {
        if($this->firstnameFilter == snf::ALL)
        {
            return $students;
        }
        else 
        {
            $students = $this->filter_by_firstname_letter($students);
        }

        return $students;
    }

    private function filter_by_firstname_letter($students)
    {
        $filtered = array();

        foreach($students as $student)
        {
            $firstLetter = mb_substr($student->firstname, 0, 1);

            if($firstLetter == $this->firstnameFilter)
            {
                $filtered[] = $student;
            }
        }

        return $filtered;
    }


}