<?php

use coursework_lib as lib;

class ManagerWorkComplition extends WorkCompletion
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
        return $this->get_back_works_list_button();
    }

    private function get_back_works_list_button() : string 
    {
        $btn = '<a href="/mod/coursework/view.php?id='.$this->cm->id.'">';
        $btn.= '<button form="sdvsre453">'.get_string('back_to_works_list', 'coursework').'</button>';
        $btn.= '</a>';
        return $btn;
    }



}

