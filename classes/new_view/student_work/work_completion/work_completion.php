<?php

use coursework_lib as lib;
use view_lib as view;

abstract class WorkCompletion 
{
    protected $course;
    protected $cm;
    protected $studentId;

    function __construct(stdClass $course, stdClass $cm, int $studentId)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->studentId = $studentId;
    }

    public function get_page() : string 
    {
        $page = $this->get_page_header();
        $page.= $this->get_guidelines();
        $page.= $this->get_done_work();

        if(view\is_coursework_use_task($this->cm))
        {
            $page.= $this->get_task_completion();
        }
        

        return $page;
    }

    private function get_page_header() : string 
    {
        $header = '<h3>'.get_string('pluginname', 'coursework');
        $user = lib\get_user($this->studentId);
        $header.= ' <b>'.$user->lastname.' '.$user->firstname.'</b></h3>';
        return $header;
    }

    private function get_guidelines() : string 
    {
        $guidelines = new Guidelines($this->course, $this->cm, $this->studentId, false);
        return $guidelines->get_module();
    }

    private function get_done_work() : string 
    {
        $doneWork = new DoneWork($this->course, $this->cm, $this->studentId, false);
        return $doneWork->get_module();
    }

    private function get_task_completion() : string 
    {
        $doneWork = new TaskCompletion($this->course, $this->cm, $this->studentId, false);
        return $doneWork->get_module();
    }




}