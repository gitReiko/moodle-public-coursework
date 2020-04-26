<?php

use coursework_lib as lib;
use view_lib as view;

abstract class AssignCustomTask 
{
    protected $course;
    protected $cm;
    protected $studentId;
    protected $formName = 'custom_form';

    function __construct(stdClass $course, stdClass $cm, int $studentId)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->studentId = $studentId;
    }

    public function get_page() : string 
    {
        $page = $this->get_page_header();
        $page.= $this->get_description_field();
        $page.= $this->get_task_sections_list();
        $page.= $this->get_add_section_button();
        $page.= $this->get_button_block();
        $page.= $this->get_custom_assignment_form();
        return $page;
    }

    abstract protected function get_page_header() : string;

    private function get_description_field() : string 
    {
        $field = '<h4>'.get_string('description', 'coursework').'</h4>';
        $field.= '<p>'.$this->get_description_textarea().'</p>';
        return $field; 
    }

    private function get_description_textarea() : string 
    {
        $area = '<textarea name="'.DESCRIPTION.'" cols="80" rows="5" ';
        $area.= ' form="'.$this->formName.'">';
        $area.= $this->get_description_value();
        $area.= '</textarea>';
        return $area;
    }

    abstract protected function get_description_value() : string;

    private function get_task_sections_list() : string 
    {
        $table = '<h4>'.get_string('task_sections_list', 'coursework').'</h4>';
        $table.= '<table class="simple_table">';
        $table.= $this->get_task_sections_list_header();
        $table.= $this->get_task_sections_list_body();
        $table.= '</table>';
        return $table;
    }
    private function get_task_sections_list_header() : string 
    {
        $header = '<thead><tr class="header">';
        $header.= '<td>'.get_string('name', 'coursework').'</td>';
        $header.= '<td>'.get_string('completion_date', 'coursework'). '</td>';
        $header.= '<td></td>';
        $header.= '</tr></thead>';
        return $header;
    }

    private function get_task_sections_list_body() : string 
    {
        $tbody = '<tbody id="sections_container">';
        $tbody.= $this->get_tbody_sections();
        $tbody.= '</tbody>';
        return $tbody;
    }

    abstract protected function get_tbody_sections() : string;

    private function get_add_section_button() : string 
    {
        return '<button onclick="CustomTaskPage.add_section()">'.get_string('add_task_section', 'coursework').'</button>';
    }

    private function get_button_block() : string 
    {
        $block = '<table><tr>';
        $block.= '<td>'.$this->get_give_task_button().'</td>';
        $block.= '<td>'.$this->get_back_button().'</td>';
        $block.= '</tr></table>';
        return $block;
    }

    private function get_give_task_button() : string 
    {
        $btn = '<button onclick="return CustomTaskPage.validate_form()" ';
        $btn.= 'form="'.$this->formName.'">';
        $btn.= get_string('give_task', 'coursework');
        $btn.= '</button>';
        return $btn;
    }

    private function get_back_button() : string 
    {
        $btn = '<form>';
        $btn.= '<input type="hidden" name="'.ID.'" value="'.$this->cm->id.'"/>';
        $btn.= '<input type="hidden" name="'.ViewMain::GUI_EVENT.'" value="'.ViewMain::USER_WORK.'">';
        $btn.= '<input type="hidden" name="'.STUDENT.ID.'" value="'.$this->studentId.'">';
        $btn.= '<button>'.get_string('back', 'coursework').'</button>';
        $btn.= '</form>';
        return $btn;
    }

    private function get_custom_assignment_form() : string 
    {
        $form = '<form id="'.$this->formName.'" >';
        $form.= '<input type="hidden" name="'.ID.'" value="'.$this->cm->id.'"/>';
        $form.= '<input type="hidden" name="'.STUDENT.ID.'" value="'.$this->studentId.'">';
        $form.= '<input type="hidden" name="'.DB_EVENT.'" value="'.ViewDatabaseHandler::CUSTOM_TASK_ASSIGNMENT.'">';
        $form.= '</form>';   
        return $form;
    }


   


}