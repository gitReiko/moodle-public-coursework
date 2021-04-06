<?php

namespace View\StudentsWorksList;

use Coursework\Lib\Getters\TeachersGetter as tg;

class TeachersGetter 
{
    private $course;
    private $cm;

    private $students;
    private $leaders;

    function __construct(\stdClass $course, \stdClass $cm, $students) 
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->students = $students;
        $this->init_leaders();
    }

    public function get_leaders() 
    {
        return $this->leaders;
    }

    private function init_leaders()
    {
        $teachers = tg::get_teachers($this->cm->instance);
        /*
        $teachers = tg::add_not_configurated_teachers_from_students_array(
            $this->cm->instance, $teachers, $this->students
        );
        */


        $this->leaders = $teachers;
    }




}
