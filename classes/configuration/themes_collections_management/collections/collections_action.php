<?php

use coursework_lib as lib;

abstract class CollectionsAction 
{
    protected $course;
    protected $cm;

    protected $courses;

    private $backToOverviewFormName = 'backToOverviewForm';

    const ACTION_FORM = 'action_form';

    function __construct($course, $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->courses = lib\get_all_site_courses();
    }

    public function get_gui() : string 
    {
        $gui = '';
        $gui.= $this->get_html_form_start();
        $gui.= $this->get_action_header();
        $gui.= $this->get_name_field();
        $gui.= $this->get_course_field();
        $gui.= $this->get_description_field();
        $gui.= $this->get_buttons_panel();
        $gui.= $this->get_form_hidden_inputs();
        $gui.= $this->get_html_form_end();
        $gui.= $this->get_back_to_overview_form();
     
        return $gui;
    }
    
    private function get_html_form_start() : string { return '<form id="'.self::ACTION_FORM.'">'; }

    abstract protected function get_action_header() : string;

    private function get_name_field() : string 
    {
        $field = '<h4>'.get_string('name', 'coursework').'</h4>';
        $field.= '<p>'.$this->get_name_input().'</p>';
        return $field;
    }

    private function get_name_input() : string 
    {
        $input = '<input type="text" name="'.NAME.'" ';
        $input.= ' value="'.$this->get_name_input_value().'" ';
        $input.= ' minlength="1" maxlength="254" size="80" required autofocus>';
        return $input;
    }

    abstract protected function get_name_input_value() : string;

    private function get_course_field() : string 
    {
        $field = '<h4>'.get_string('course', 'coursework').'</h4>';
        $field.= '<p>'.$this->get_course_select().'</p>';
        return $field;
    }

    private function get_course_select() : string 
    {
        $select = '<select name="'.COURSE.'" autocomplete="off">';
        foreach($this->courses as $course)
        {
            $select.= "<option value='{$course->id}'";

            if($this->is_course_selected($course->id)) $select.= ' selected ';
            
            $select.=">{$course->fullname}</option>";
        }
        $select.= '</select>';
        return $select;
    }

    abstract protected function is_course_selected(int $courseId) : bool;

    private function get_description_field() : string 
    {
        $field = '<h4>'.get_string('description', 'coursework').'</h4>';
        $field.= '<p>'.$this->get_description_textarea().'</p>';
        return $field; 
    }

    private function get_description_textarea() : string 
    {
        $area = '<textarea name="'.DESCRIPTION.'" cols="80" rows="5">';
        $area.= $this->get_description_text();
        $area.= '</textarea>';
        return $area;
    }

    abstract protected function get_description_text() : string;

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
        $params = '<input type="hidden" name="id" value="'.$this->cm->id.'" >';
        $params.= '<input type="hidden" name="'.CONFIG_MODULE.'" value="'.THEMES_COLLECTIONS_MANAGEMENT.'">';
        $params.= '<input type="hidden" name="'.LeadersSetting::GUI_TYPE.'" value="'.CollectionsManagement::OVERVIEW.'">';
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
        $button.= '<input type="hidden" name="'.CONFIG_MODULE.'" value="'.THEMES_COLLECTIONS_MANAGEMENT.'">';
        $button.= '<input type="hidden" name="'.LeadersSetting::GUI_TYPE.'" value="'.CollectionsManagement::OVERVIEW.'">';
        $button.= '</form>';
        return $button;
    }


} 

