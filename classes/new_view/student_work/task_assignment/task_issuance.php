<?php



use coursework_lib as lib;

class TaskIssuance 
{
    private $course;
    private $cm;
    private $studentId;

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

        return $page;
    }

    private function get_page_header() : string 
    {
        return '<h3>'.get_string('task_issuance_header', 'coursework').'</h3>';
    }

    private function get_guidelines() : string 
    {
        $guidelines = new Guidelines($this->course, $this->cm);
        return $guidelines->get_module();
    }



}