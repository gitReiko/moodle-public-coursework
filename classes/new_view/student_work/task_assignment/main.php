<?php

//require_once 'not_available_page.php';
//require_once 'theme_selection_page.php';

use coursework_lib as lib;

class TaskAssignmentMain 
{
    private $course;
    private $cm;
    private $studentId;

    function __construct(stdClass $course, stdClass $cm, int $studentId)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->studentId = $studentId;
    }

    public function get_page() : string 
    {
        global $USER;
        if(lib\is_user_teacher($this->cm, $USER->id))
        {
            return 'teacher';
        }
        else 
        {
            return 'student page';
        }
    }


}