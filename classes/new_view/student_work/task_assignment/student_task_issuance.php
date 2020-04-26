<?php

use coursework_lib as lib;

class StudentTaskIssuance extends TaskIssuance 
{
    private $work;

    function __construct(stdClass $course, stdClass $cm, int $studentId)
    {
        parent::__construct($course, $cm, $studentId);

        $this->work = lib\get_student_work($this->cm, $this->studentId);
    }

    protected function init_open_blocks() : void
    {
        $this->openGuidlines = true;
        $this->openDoneWork = true;
        $this->openTaskTemplate = true;
    }

    protected function get_page_header() : string 
    {
        $header = '<h3>';
        $header.= get_string('waiting_for_task_from', 'coursework');
        $user = lib\get_user($this->work->teacher);
        $header.= '<b> '.$user->lastname.' '.$user->firstname.'</b>';
        $header.= '</h3>';
        return $header;
    }

    protected function get_footer() : string 
    {
        $footer = lib\get_back_to_course_button($this->course->id);
        return $footer;
    }

}
