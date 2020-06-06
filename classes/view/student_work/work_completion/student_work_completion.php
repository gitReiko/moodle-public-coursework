<?php

use coursework_lib as lib;

class StudentWorkComplition extends WorkCompletion
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
        $modules = '';

        if(lib\is_student_work_not_ready_or_need_to_fix($this->cm, $this->studentId))
        {
            $modules.= $this->get_send_for_check_module();
        }
        $modules.= '<p>'.lib\get_back_to_course_button($this->course->id).'</p>';

        return $modules;
    }

    private function get_send_for_check_module() : string 
    {
        $studentActions = new SendForCheck($this->course, $this->cm, $this->studentId, true);
        return $studentActions->get_module();
    }





}

