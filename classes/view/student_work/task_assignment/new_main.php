<?php

namespace Coursework\View\StudentWork\TaskAssignment;

use Coursework\View\StudentsWork\Components as c;
use Coursework\Lib\Getters\CommonGetter as cg;

class Main 
{
    private $course;
    private $cm;
    private $studentId;

    function __construct(\stdClass $course, \stdClass $cm, int $studentId)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->studentId = $studentId;
    }

    public function get_page() : string 
    {
        $page = cg::get_page_header($this->cm);
        $page.= $this->get_info_block();
        $page.= $this->get_guidelines_block();
        $page.= $this->get_chat_block();

        return $page;
    }

    private function get_info_block() : string 
    {
        $info = new c\Info($this->course, $this->cm, $this->studentId);
        return $info->get_component();
    }

    private function get_guidelines_block() : string 
    {
        $guidelines = new c\Guidelines($this->course, $this->cm, $this->studentId);
        return $guidelines->get_component();
    }

    private function get_chat_block() : string 
    {
        $chat = new c\Chat($this->course, $this->cm, $this->studentId);
        return $chat->get_component();
    }




}