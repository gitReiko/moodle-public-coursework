<?php

use coursework_lib as lib;

class Guidelines extends ViewModule 
{

    protected function get_module_name() : string
    {
        return 'guidelines';
    }

    protected function get_module_header() : string
    {
        return get_string('guidelines', 'coursework');
    }

    protected function get_module_body() : string
    {
        $coursework = lib\get_coursework($this->cm->instance);
        return format_module_intro('coursework', $coursework, $this->cm->id);
    }

}


/*
class Guidelines 
{
    private $course;
    private $cm;
    private $studentId;

    function __construct(stdClass $course, stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;
    }

    public function get_module() : string 
    {
        $page = $this->get_start_of_container();
        $page.= $this->get_container_header();
        $page.= $this->get_container_body();
        $page.= $this->get_end_of_container();
        return $page;
    }

    private function get_page_header() : string 
    {
        $coursework = lib\get_coursework($this->cm->instance);
        return format_module_intro('coursework', $coursework, $this->cm->id);
    }

    private function get_start_of_container() : string 
    {
        return '<div class="guidelines_container">';
    }

    private function get_container_header() : string 
    {
        $header = '<div id="guidelines_header" onclick="hide_or_show_block(`guidelines_body`)">';
        $header.= get_string('guidelines', 'coursework');
        $header.= '</div>';
        return $header;
    }

    private function get_container_body() : string 
    {
        $coursework = lib\get_coursework($this->cm->instance);

        $body = '<div id="guidelines_body">';
        $body.= format_module_intro('coursework', $coursework, $this->cm->id);
        $body.= '</div>';

        return $body;
    }

    private function get_end_of_container() : string 
    {
        return '</div>';
    }




}
*/