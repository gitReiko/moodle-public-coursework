<?php

use coursework_lib as lib;

abstract class LeadersActionGUI 
{
    protected $course;
    protected $cm;

    protected $courseTeachers;
    protected $siteCourses;

    const ACTION_FORM = 'action_form';

    function __construct($course, $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->courseTeachers = lib\get_all_course_teachers($this->cm);
        $this->siteCourses = $this->get_all_site_courses();
    }

    public function display() : string 
    {
        $gui = '';
        $gui.= $this->get_html_form_start();
        $gui.= $this->get_action_header();
        $gui.= $this->get_leader_label();
        $gui.= $this->get_leader_select();
        $gui.= $this->get_course_label();
        $gui.= $this->get_course_select();
        $gui.= $this->get_quota_label();
        $gui.= $this->get_quota_input();
        $gui.= $this->get_form_common_params();
        $gui.= $this->get_form_special_params();
        $gui.= $this->get_html_form_end();
        $gui.= $this->get_buttons_panel();
     
        return $gui;
    }

    private function get_all_site_courses() : array
    {
        global $DB;
        $courses = array();
        $courses = $DB->get_records('course', array(), 'fullname', 'id, fullname');
        return $courses;
    }

    private function get_html_form_start() : string { return '<form id="'.self::ACTION_FORM.'">'; }

    abstract protected function get_action_header() : string;

    private function get_label(string $text, string $title = null) : string 
    {
        if($title) $label = '<h4 title="'.$title.'">';
        else $label = '<h4>';
        $label.= $text.'</h4>';
        return $label;
    }

    private function get_leader_label() : string 
    {
        $text = get_string('leader', 'coursework');
        return $this->get_label($text);
    }

    protected function get_select(array $items, string $selectName, int $selectedItem = null) : string 
    {
        if($selectedItem) return $this->get_select_with_selected_item($items, $selectName, $selectedItem);
        else return $this->get_select_without_selected_item($items, $selectName, $selectedItem);
    }

    private function get_select_with_selected_item(array $items, string $selectName, int $selectedItem = null) : string 
    {
        $select = '<p><select name="'.$selectName.'" autocomplete="off">';
        foreach($items as $item)
        {
            $select.= '<option value="'.$item->id.'" ';
            if($selectedItem == $item->id) $select.= ' selected>';
            else $select.= '>';
            $select.= $item->fullname;
            $select.= '</option>';          
        }
        $select.= '</select></p>';
        return $select;       
    }

    private function get_select_without_selected_item(array $items, string $selectName, int $selectedItem = null) : string 
    {
        $select = '<p><select name="'.$selectName.'" autocomplete="off">';
        foreach($items as $item)
        {
            $select.= '<option value="'.$item->id.'">';
            $select.= $item->fullname;
            $select.= '</option>';          
        }
        $select.= '</select></p>';
        return $select;       
    }

    abstract protected function get_leader_select() : string;

    private function get_course_label() : string 
    {
        $text = get_string('course', 'coursework');
        return $this->get_label($text);
    }

    abstract protected function get_course_select() : string;

    private function get_quota_label() : string 
    {
        $text = get_string('quota', 'coursework').' (?)';
        $title = get_string('quota_title', 'coursework');
        return $this->get_label($text, $title);
    }

    abstract protected function get_quota_input() : string;

    private function get_form_common_params() : string 
    {
        $params = '<input type="hidden" name="id" value="'.$this->cm->id.'" >';
        $params.= '<input type="hidden" name="'.CONFIG_MODULE.'" value="'.LEADERS_SETTING.'">';
        return $params;
    }

    abstract protected function get_form_special_params() : string;

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
        $button = '<p><form>';
        $button.= '<input type="submit" value="'.get_string('back', 'coursework').'">';
        $button.= '<input type="hidden" name="id" value="'.$this->cm->id.'" >';
        $button.= '<input type="hidden" name="'.CONFIG_MODULE.'" value="'.LEADERS_SETTING.'">';
        $button.= '<input type="hidden" name="'.LeadersSetting::GUI_TYPE.'" value="'.LeadersSetting::OVERVIEW.'">';
        $button.= '</form></p>';
        return $button;
    }

    private function get_html_form_end() : string { return '</form>'; }
    
} 

