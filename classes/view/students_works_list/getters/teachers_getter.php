<?php

namespace Coursework\View\StudentsWorksList;

use Coursework\View\StudentsWorksList\TeachersSelector as ts;
use Coursework\View\StudentsWorksList\MainGetter as mg;
use Coursework\Lib\Getters\TeachersGetter as tg;

class TeachersGetter 
{
    private $course;
    private $cm;

    private $teachers;
    private $selectedTeacherId;

    function __construct(\stdClass $course, \stdClass $cm) 
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->init_teachers();
        $this->init_selected_teacher_id();
    }

    public function get_teachers() 
    {
        return $this->teachers;
    }

    public function get_selected_teacher_id()
    {
        return $this->selectedTeacherId;
    }

    public function filter_out_not_student_teachers($students)
    {
        $filtered = array();

        foreach($this->teachers as $teacher)
        {
            if($this->is_teacher_is_student_leader($teacher, $students)
                || $this->is_teachers_is_all_teachers($teacher))
            {
                $filtered[] = $teacher;
            }
        }

        return $filtered;
    }

    private function is_teachers_is_all_teachers($teacher) : bool 
    {
        if($teacher->id == mg::ALL_TEACHERS)
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    private function is_teacher_is_student_leader($teacher, $students) : bool 
    {
        foreach($students as $student)
        {
            if($student->teacher == $teacher->id)
            {
                return true;
            }
        }

        return false;
    }

    private function init_teachers() 
    {
        $teachers = tg::get_coursework_teachers($this->cm->instance);
        $teachers = $this->add_all_teachers_item_to_teachers($teachers);

        $this->teachers = $teachers;
    }

    private function add_all_teachers_item_to_teachers($teachers)
    {
        $allTeachers = array($this->get_all_teachers_item());
        return array_merge($allTeachers, $teachers);
    }

    private function get_all_teachers_item() : \stdClass 
    {
        $allTeachers = new \stdClass;
        $allTeachers->id = mg::ALL_TEACHERS;
        $allTeachers->firstname = get_string('all_teachers', 'coursework');
        $allTeachers->lastname = '';
        $allTeachers->email = '';
        $allTeachers->phone1 = '';
        $allTeachers->phone2 = '';

        return $allTeachers;
    }

    private function init_selected_teacher_id()
    {
        $teacherIdFromPost = optional_param(ts::TEACHER, null, PARAM_INT);

        if(empty($teacherIdFromPost))
        {
            if($this->is_user_teacher())
            {
                $this->selectedTeacherId = $this->get_user_id();
            }
            else 
            {
                $this->selectedTeacherId = mg::ALL_TEACHERS;
            }
        }
        else 
        {
            $this->selectedTeacherId = $teacherIdFromPost;
        }
    }

    private function is_user_teacher() : bool 
    {
        global $USER;

        foreach($this->teachers as $teacher)
        {
            if($teacher->id == $USER->id)
            {
                return true;
            }
        }

        return false;
    }

    private function get_user_id() : int 
    {
        global $USER;
        return $USER->id;
    }

}
