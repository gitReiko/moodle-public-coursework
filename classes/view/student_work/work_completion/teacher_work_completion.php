<?php

use coursework_lib as lib;

class TeacherWorkComplition extends WorkCompletion
{
    protected $course;
    protected $cm;
    protected $studentId;

    function __construct(stdClass $course, stdClass $cm, int $studentId)
    {
        parent::__construct($course, $cm, $studentId);


    }

    protected function get_additional_modules() : string
    {
        $str = $this->get_work_check_module();
        $str.= '<p>'.lib\get_back_to_works_list_button($this->cm).'</p>';
        return $str;
    }

    private function get_work_check_module() : string 
    {
        $workCheck = new WorkCheck($this->course, $this->cm, $this->studentId, true);
        return $workCheck->get_module();
    }



}

