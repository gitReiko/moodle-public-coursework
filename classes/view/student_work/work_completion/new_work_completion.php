<?php 

namespace Coursework\View\StudentWork;

use Coursework\View\StudentsWork\Components as c;
use Coursework\Lib\Getters\CommonGetter as cg;

class NewWorkCompletion
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
        $str = cg::get_page_header($this->cm);

        $str.= $this->get_info_block();
        $str.= $this->get_guidelines_block();
        $str.= $this->get_chat_block();
        $str.= $this->get_filemanager_block();

        return $str;
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

    private function get_filemanager_block() : string 
    {
        $chat = new c\Filemanager($this->course, $this->cm, $this->studentId);
        return $chat->get_component();
    }




}
