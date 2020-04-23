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
        $page.= $this->get_done_work();
        $page.= $this->get_task_template();

        return $page;
    }

    private function get_page_header() : string 
    {
        $header = '<h3>';
        $header.= get_string('task_issuance_header', 'coursework');
        $user = lib\get_user($this->studentId);
        $header.= '<b> '.$user->lastname.' '.$user->firstname.'</b>';
        $header.= '</h3>';
        return $header;
    }

    private function get_guidelines() : string 
    {
        $guidelines = new Guidelines($this->course, $this->cm, $this->studentId);
        return $guidelines->get_module();
    }

    private function get_done_work() : string 
    {
        $doneWork = new DoneWork($this->course, $this->cm, $this->studentId, true);
        return $doneWork->get_module();
    }

    private function get_task_template() : string 
    {
        $taskTemplate = new TaskTemplate($this->course, $this->cm, $this->studentId, true);
        return $taskTemplate->get_module();
    }



}