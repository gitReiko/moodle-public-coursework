<?php

namespace view\quota_overview;
use coursework_lib as lib;

class Getter 
{

    private $cm;
    private $teachers;

    function __construct(\stdClass $cm) 
    {
        $this->cm = $cm;
        $this->init_teachers();
    }

    public function get_teachers() : array 
    {
        return $this->teachers;
    }

    private function init_teachers() : void
    {
        $this->teachers = lib\get_coursework_teachers($this->cm->instance);
    }




}