<?php

namespace Coursework\View\StudentsWorksList;

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



}
