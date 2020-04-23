<?php

use coursework_lib as lib;

abstract class ViewModule 
{
    protected $course;
    protected $cm;

    protected $moduleName;
    protected $displayBlock;

    function __construct(stdClass $course, stdClass $cm, bool $displayBlock = false)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->moduleName = $this->get_module_name();
        $this->displayBlock = $displayBlock;
    }

    public function get_module() : string 
    {
        $page = $this->get_start_of_container();
        $page.= $this->get_container_header();
        $page.= $this->get_container_body();
        $page.= $this->get_end_of_container();
        return $page;
    }

    abstract protected function get_module_name() : string;

    private function get_start_of_container() : string 
    {
        return "<div class='{$this->moduleName}_container'>";
    }

    private function get_container_header() : string 
    {
        $header = "<div id='{$this->moduleName}_header' class='view_container_header'";
        $header.= "onclick='hide_or_show_block(`{$this->moduleName}_body`)'>";
        $header.= $this->get_module_header().$this->get_click_title();
        $header.= '</div>';
        return $header;
    }

    private function get_click_title() : string 
    {
        $title = ' <span class="view_container_header_title">(';
        $title.= get_string('click_to_show_hide', 'coursework');
        $title.= ')</span>';
        return $title;
    }

    abstract protected function get_module_header() : string;

    private function get_container_body() : string 
    {
        $body = "<div id='{$this->moduleName}_body' class='view_container_body'";
        if(!$this->displayBlock) $body.= ' style="display: none;" ';
        $body.= "'>";
        $body.= $this->get_module_body();
        $body.= '</div>';

        return $body;
    }

    abstract protected function get_module_body() : string;

    private function get_end_of_container() : string 
    {
        return '</div>';
    }

}