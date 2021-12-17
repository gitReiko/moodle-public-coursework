<?php

namespace Coursework\View\TasksTemplatesManagement\Sections;

use Coursework\View\TasksTemplatesManagement\Main;
use Coursework\View\TasksTemplatesManagement\Lib;

abstract class Action
{
    protected $course;
    protected $cm;

    private $backToOverviewFormName = 'backToOverviewForm';

    private $task;

    const ACTION_FORM = 'action_form';

    function __construct($course, $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->task = Lib::get_task_from_post();
    }

    public function get_gui() : string 
    {
        $gui = '';
        $gui.= $this->get_html_form_start();
        $gui.= $this->get_action_header();
        $gui.= $this->get_name_field();
        $gui.= $this->get_description_field();
        $gui.= $this->list_position_field();
        $gui.= $this->get_completion_date_field();
        $gui.= $this->get_buttons_panel();
        $gui.= $this->get_form_hidden_inputs();
        $gui.= $this->get_html_form_end();
        $gui.= $this->get_back_to_overview_form();
     
        return $gui;
    }
    
    private function get_html_form_start() : string { return '<form id="'.self::ACTION_FORM.'" method="post">'; }

    abstract protected function get_action_header() : string;

    private function get_name_field() : string 
    {
        $field = '<h4>'.get_string('name', 'coursework').'</h4>';
        $field.= '<p>'.$this->get_name_input().'</p>';
        return $field;
    }

    private function get_name_input() : string 
    {
        $input = '<input type="text" name="'.Main::NAME.'" ';
        $input.= ' value="'.$this->get_name_input_value().'" ';
        $input.= ' minlength="1" maxlength="254" size="80" required autofocus autocomplete="off">';
        return $input;
    }

    abstract protected function get_name_input_value() : string;

    private function get_description_field() : string 
    {
        $field = '<h4>'.get_string('description', 'coursework').'</h4>';
        $field.= '<p>'.$this->get_description_textarea().'</p>';
        return $field; 
    }

    private function get_description_textarea() : string 
    {
        $area = '<textarea name="'.Main::DESCRIPTION.'" cols="80" rows="5" autocomplete="off">';
        $area.= $this->get_description_text();
        $area.= '</textarea>';
        return $area;
    }

    abstract protected function get_description_text() : string;

    private function list_position_field() : string 
    {
        $field = '<h4>'.get_string('position_in_task_list', 'coursework').'</h4>';
        $field.= '<p>'.$this->get_list_position_input().'</p>';
        return $field; 
    }

    private function get_list_position_input() : string 
    {
        $input = '<input type="number" name="'.Main::LIST_POSITION.'" ';
        $input.= ' value ="'.$this->get_list_position_input_value().'"';
        $input.= ' autocomplete="off" min="1" max="65530">';
        return $input;   
    }

    abstract protected function get_list_position_input_value() : string;

    private function get_completion_date_field() : string
    {
        $field = '<h4>'.get_string('completion_date', 'coursework').'</h4>';
        $field.= '<p>'.$this->get_completion_date_input().'</p>';
        return $field; 
    }

    private function get_completion_date_input() : string 
    {
        $input = '<input type="date" name="'.Main::COMPLETION_DATE.'" ';
        $input.= ' value ="'.$this->get_completion_date_value().'"';
        $input.= ' autocomplete="off" >';
        return $input;
    }

    abstract protected function get_completion_date_value() : string;

    private function get_buttons_panel() : string 
    {
        $btns = '<table class="btns_panel"><tr>';
        $btns.= '<td>'.$this->get_action_button().'</td>';
        $btns.= '<td>'.$this->get_back_to_overview_button().'</td>';
        $btns.= '</tr></table>';
        return $btns;
    }

    abstract protected function get_action_button() : string ;

    private function get_back_to_overview_button() : string 
    {
        return '<p><input type="submit" value="'.get_string('back', 'coursework').'" form="'.$this->backToOverviewFormName.'"></p>';
    }

    private function get_form_hidden_inputs() : string 
    {
        $params = '<input type="hidden" name="'.Main::ID.'" value="'.$this->cm->id.'" >';
        $params.= '<input type="hidden" name="'.Main::GUI_TYPE.'" value="'.Main::SECTIONS_MANAGEMENT.'">';
        $params.= '<input type="hidden" name="'.Main::TASK_ID.'" value="'.$this->task->id.'">';
        $params.= $this->get_unique_form_hidden_inputs();
        return $params;
    }

    abstract protected function get_unique_form_hidden_inputs() : string;

    private function get_html_form_end() : string 
    { 
        return '</form>'; 
    }

    private function get_back_to_overview_form() : string 
    {
        $button = "<form id='{$this->backToOverviewFormName}' method='post'>";
        $button.= '<input type="hidden" name="'.Main::ID.'" value="'.$this->cm->id.'" >';
        $button.= '<input type="hidden" name="'.Main::GUI_TYPE.'" value="'.Main::SECTIONS_MANAGEMENT.'">';
        $button.= '<input type="hidden" name="'.Main::TASK_ID.'" value="'.$this->task->id.'">';
        $button.= '</form>';
        return $button;
    }


} 

