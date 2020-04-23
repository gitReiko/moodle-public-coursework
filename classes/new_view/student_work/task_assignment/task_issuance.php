<?php

use coursework_lib as lib;
use view_lib as view;

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
        $page.= $this->get_action_buttons();
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

    private function get_action_buttons() : string 
    {
        $btns = '<table><tr>';
        $btns.= $this->get_use_template_button();
        $btns.= $this->get_correct_template_button();
        $btns.= $this->get_create_task_button();
        $btns.= view\get_back_to_works_list_button($this->cm);
        $btns.= '</tr></table>';
        return $btns;
    }

    private function get_use_template_button() : string 
    {
        $btn = '<td>';
        $btn.= '<form>';
        $btn.= '<input type="hidden" name="'.ID.'" value="'.$this->cm->id.'"/>';
        $btn.= '<input type="hidden" name="'.DB_EVENT.'" value="'.ViewDatabaseHandler::USE_TASK_TEMPLATE.'">';
        $btn.= '<input type="hidden" name="'.STUDENT.ID.'" value="'.$this->studentId.'">';
        $btn.= '<button>';
        $btn.= get_string('use_task_template', 'coursework');
        $btn.= '</button>';
        $btn.= '</form>';
        $btn.= '</td>';
        return $btn;
    }

    private function get_correct_template_button() : string 
    {
        $btn = '<td>';
        $btn.= '<button>';
        $btn.= get_string('correct_template', 'coursework');
        $btn.= '</button>';
        $btn.= '</td>';
        return $btn;
    }

    private function get_create_task_button() : string 
    {
        $btn = '<td>';
        $btn.= '<button>';
        $btn.= get_string('create_new_task', 'coursework');
        $btn.= '</button>';
        $btn.= '</td>';
        return $btn;
    }



}