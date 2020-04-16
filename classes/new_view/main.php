<?php

require_once 'works_list.php';
require_once 'student_work/main.php';

use coursework_lib as lib;

class ViewMain 
{
    private $course;
    private $cm;

    function __construct(stdClass $course, stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

    }

    public function get_gui() : string 
    {
        global $USER;

        if(lib\is_user_student($this->cm, $USER->id))
        {
            return $this->get_student_work_gui();
        }
        else 
        {
            return $this->get_students_works_list_gui();
        }
    }

    private function get_students_works_list_gui() : string 
    {
        $worksList = new WorksList($this->course, $this->cm);
        return $worksList->get_gui();
    }

    private function get_student_work_gui() : string 
    {
        global $USER;
        $worksList = new StudentWorkMain($this->course, $this->cm, $USER->id);
        return $worksList->get_gui();
    }


}

