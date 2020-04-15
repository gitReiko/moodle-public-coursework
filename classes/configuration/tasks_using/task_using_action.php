<?php

use coursework_lib as lib;

abstract class TaskUsingAction 
{
    protected $course;
    protected $cm;

    protected $templates;

    private $backToOverviewFormName = 'backToOverviewForm';

    const ACTION_FORM = 'action_form';

    function __construct($course, $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->tasks = $this->get_task_templates();
    }

    public function get_gui() : string 
    {
        $gui = '';
        $gui.= $this->get_html_form_start();
        $gui.= $this->get_action_header();
        $gui.= $this->get_task_template_field();
        $gui.= $this->get_buttons_panel();
        $gui.= $this->get_form_hidden_inputs();
        $gui.= $this->get_html_form_end();
        $gui.= $this->get_back_to_overview_form();
     
        return $gui;
    }

    private function get_task_templates()
    {
        global $DB;
        return $DB->get_records('coursework_tasks', array('template' => 1), 'name');
    }
    
    private function get_html_form_start() : string { return '<form id="'.self::ACTION_FORM.'">'; }

    private function get_action_header() : string
    {
        return '<h3>'.get_string('used_task_template_selecting', 'coursework').'</h3>';
    }

    private function get_task_template_field() : string 
    {
        $field = '<h4>'.get_string('task_template', 'coursework').'</h4>';
        $field.= '<p>'.$this->get_task_template_select().'</p>';
        return $field;
    }

    private function get_task_template_select() : string 
    {
        $select = '<select name="'.TASK.'" autocomplete="off">';
        foreach($this->tasks as $task)
        {
            $select.= "<option value='{$task->id}'";

            if($this->is_task_selected($task->id)) $select.= ' selected ';
            
            $select.=">{$task->name}</option>";
        }
        $select.= '</select>';
        return $select;
    }

    abstract protected function is_task_selected(int $taskId) : bool;

    private function get_buttons_panel() : string 
    {
        $btns = '<table class="btns_panel"><tr>';
        $btns.= '<td>'.$this->get_action_button().'</td>';
        $btns.= '<td>'.$this->get_back_to_overview_button().'</td>';
        $btns.= '</tr></table>';
        return $btns;
    }

    private function get_action_button() : string
    {
        return '<p><input type="submit" value="'.get_string('select_task_template', 'coursework').'" ></p>';
    }

    private function get_back_to_overview_button() : string 
    {
        return '<p><input type="submit" value="'.get_string('back', 'coursework').'" form="'.$this->backToOverviewFormName.'"></p>';
    }

    private function get_form_hidden_inputs() : string 
    {
        $params = '<input type="hidden" name="id" value="'.$this->cm->id.'" >';
        $params.= '<input type="hidden" name="'.CONFIG_MODULE.'" value="'.TASKS_USING.'">';
        $params.= '<input type="hidden" name="'.LeadersSetting::GUI_TYPE.'" value="'.TasksUsingMain::OVERVIEW.'">';
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
        $button = "<form id='{$this->backToOverviewFormName}'>";
        $button.= '<input type="hidden" name="id" value="'.$this->cm->id.'" >';
        $button.= '<input type="hidden" name="'.CONFIG_MODULE.'" value="'.TASKS_USING.'">';
        $button.= '<input type="hidden" name="'.LeadersSetting::GUI_TYPE.'" value="'.TasksUsingMain::OVERVIEW.'">';
        $button.= '</form>';
        return $button;
    }


} 

