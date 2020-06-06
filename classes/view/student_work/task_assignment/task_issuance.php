<?php

use coursework_lib as lib;
use view_lib as view;

abstract class TaskIssuance 
{
    protected $course;
    protected $cm;
    protected $studentId;

    protected $openGuidlines;
    protected $openDoneWork;
    protected $openTaskTemplate;

    function __construct(stdClass $course, stdClass $cm, int $studentId)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->studentId = $studentId;

        $this->init_open_blocks();
    }

    public function get_page() : string 
    {
        $page = $this->get_page_header();
        $page.= $this->get_guidelines();
        $page.= $this->get_done_work();
        $page.= $this->get_task_template();
        $page.= $this->get_footer();
                
        return $page;
    }

    abstract protected function init_open_blocks() : void;

    abstract protected function get_page_header() : string;

    private function get_guidelines() : string 
    {
        $guidelines = new Guidelines($this->course, $this->cm, $this->studentId, $this->openGuidlines);
        return $guidelines->get_module();
    }

    private function get_done_work() : string 
    {
        $doneWork = new WorkInfo($this->course, $this->cm, $this->studentId, $this->openDoneWork);
        return $doneWork->get_module();
    }

    private function get_task_template() : string 
    {
        $taskTemplate = new TaskTemplate($this->course, $this->cm, $this->studentId, $this->openTaskTemplate);
        return $taskTemplate->get_module();
    }

    abstract protected function get_footer() : string;



}