<?php

namespace Coursework\View\StudentsWork;

use Coursework\Lib\Getters\CommonGetter as cg;

use coursework_lib as lib;
use view_lib as view;

abstract class WorkCompletion 
{
    protected $course;
    protected $cm;
    protected $studentId;

    function __construct(\stdClass $course, \stdClass $cm, int $studentId)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->studentId = $studentId;
    }

    public function get_page() : string 
    {
        $page = $this->get_page_header();
        $page.= $this->get_guidelines();
        $page.= $this->get_work_info();

        if(view\is_coursework_use_task($this->cm))
        {
            $page.= $this->get_task_completion();
        }

        $page.= $this->get_chat();
        $page.= $this->get_file_manager();
        $page.= $this->get_additional_modules();

        return $page;
    }

    private function get_page_header() : string 
    {
        $text = cg::get_coursework_name($this->cm->instance);
        return \html_writer::tag('h2', $text);
    }

    private function get_guidelines() : string 
    {
        $guidelines = new \Guidelines($this->course, $this->cm, $this->studentId, false);
        return $guidelines->get_module();
    }

    private function get_work_info() : string 
    {
        $workInfo = new \WorkInfo($this->course, $this->cm, $this->studentId, false);
        return $workInfo->get_module();
    }

    private function get_task_completion() : string 
    {
        $taskCompletion = new \TaskCompletion($this->course, $this->cm, $this->studentId, false);
        return $taskCompletion->get_module();
    }

    private function get_chat() : string 
    {
        $chat = new \Chat($this->course, $this->cm, $this->studentId, true);
        return $chat->get_module();
    }

    private function get_file_manager() : string 
    {
        $fileManager = new \FileManager($this->course, $this->cm, $this->studentId, true);
        return $fileManager->get_module();
    }

    abstract protected function get_additional_modules() : string;




}