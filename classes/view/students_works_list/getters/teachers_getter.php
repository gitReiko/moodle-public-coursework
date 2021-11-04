<?php

namespace Coursework\View\StudentsWorksList;

use Coursework\View\StudentsWorksList\TeachersSelector as ts;
use Coursework\View\StudentsWorksList\NewMainGetter as mg;
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
