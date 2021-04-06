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
        $this->leaders = tg::get_all_course_work_teachers($this->cm->instance);
    }




}
