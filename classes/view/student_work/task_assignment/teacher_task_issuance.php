<?php

use coursework_lib as lib;
use view_lib as view;

class TeacherTaskIssuance extends TaskIssuance 
{

    protected function init_open_blocks() : void
    {
        $this->openGuidlines = false;
        $this->openDoneWork = true;
        $this->openTaskTemplate = true;
    }

    protected function get_page_header() : string 
    {
        $header = '<h3>';
        $header.= get_string('task_issuance_header', 'coursework');
        $user = lib\get_user($this->studentId);
        $header.= '<b> '.$user->lastname.' '.$user->firstname.'</b>';
        $header.= '</h3>';
        return $header;
    }

    protected function get_footer() : string 
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
        $btn.= '<form>';
        $btn.= '<input type="hidden" name="'.ID.'" value="'.$this->cm->id.'"/>';
        $btn.= '<input type="hidden" name="'.ViewMain::GUI_EVENT.'" value="'.ViewMain::USER_WORK.'">';
        $btn.= '<input type="hidden" name="'.TaskAssignmentMain::ASSIGN_PAGE.'" value="'.TaskAssignmentMain::TEMPLATE_CORRECT.'"/>';
        $btn.= '<input type="hidden" name="'.STUDENT.ID.'" value="'.$this->studentId.'">';  
        $btn.= '<button>';
        $btn.= get_string('correct_template', 'coursework');
        $btn.= '</button>';
        $btn.= '</form>';
        $btn.= '</td>';
        return $btn;
    }

    private function get_create_task_button() : string 
    {
        $btn = '<td>';
        $btn.= '<form>';
        $btn.= '<input type="hidden" name="'.ID.'" value="'.$this->cm->id.'"/>';
        $btn.= '<input type="hidden" name="'.ViewMain::GUI_EVENT.'" value="'.ViewMain::USER_WORK.'">';
        $btn.= '<input type="hidden" name="'.TaskAssignmentMain::ASSIGN_PAGE.'" value="'.TaskAssignmentMain::NEW_TASK.'"/>';
        $btn.= '<input type="hidden" name="'.STUDENT.ID.'" value="'.$this->studentId.'">';
        $btn.= '<button>';
        $btn.= get_string('create_new_task', 'coursework');
        $btn.= '</button>';
        $btn.= '</form>';
        $btn.= '</td>';
        return $btn;
    }


}