<?php

namespace Coursework\View\ThemesCollectionsManagement\Collections;

use Coursework\View\ThemesCollectionsManagement\Main;
use Coursework\Lib\Getters\CommonGetter as cg;

abstract class Action 
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

        $this->courses = cg::get_all_site_courses();
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
    
    private function get_html_form_start() : string 
    {
        $attr = array(
            'id' => self::ACTION_FORM,
            'method' => 'post',
            'action' => Main::MODULE_URL
        );
        return \html_writer::start_tag('form', $attr);
    }

    abstract protected function get_action_header() : string;

    private function get_name_field() : string 
    {
        $name = \html_writer::tag('h4', get_string('name', 'coursework'));
        $name.= \html_writer::tag('p', $this->get_name_input());

        return $name;
    }

    private function get_name_input() : string 
    {
        $attr = array(
            'type' => 'text',
            'name' => Main::NAME,
            'value' => $this->get_name_input_value(),
            'autocomplete' => 'off',
            'minlength' => 1,
            'maxlength' => 254,
            'size' => 80,
            'required' => 'required',
            'autofocus' => 'autofocus'
        );
        return \html_writer::empty_tag('input', $attr);
    }

    abstract protected function get_name_input_value() : string;

    private function get_course_field() : string 
    {
        $course = \html_writer::tag('h4', get_string('course', 'coursework'));
        $course.= \html_writer::tag('p', $this->get_course_select());

        return $course;
    }

    private function get_course_select() : string 
    {
        $attr = array(
            'name' => Main::COURSE,
            'autocomplete' => 'off'
        );
        $select = \html_writer::start_tag('select', $attr);

        foreach($this->courses as $course)
        {
            $attr = array('value' => $course->id);

            if($this->is_course_selected($course->id))
            {
                $attr = array_merge($attr, array('selected' => 'selected'));
            }

            $text = $course->fullname;

            $select.= \html_writer::tag('option', $text, $attr);
        }

        $select.= \html_writer::end_tag('select');

        return $select;
    }

    abstract protected function is_course_selected(int $courseId) : bool;

    private function get_description_field() : string 
    {
        $desc = \html_writer::tag('h4', get_string('description', 'coursework'));
        $desc.= \html_writer::tag('p', $this->get_description_textarea());

        return $desc;
    }

    private function get_description_textarea() : string 
    {
        $attr = array(
            'name' => Main::DESCRIPTION,
            'cols' => 80,
            'rows' => 5,
            'autocomplete' => 'off'
        );
        $text = $this->get_description_text();
        return \html_writer::tag('textarea', $text, $attr);
    }

    abstract protected function get_description_text() : string;

    private function get_buttons_panel() : string 
    {
        $attr = array('class' => 'btns_panel');
        $btns = \html_writer::start_tag('table', $attr);

        $btns.= \html_writer::start_tag('table', $attr);
        $btns.= \html_writer::start_tag('tr');

        $btns.= \html_writer::tag('td', $this->get_action_button());
        $btns.= \html_writer::tag('td', $this->get_back_to_overview_button());

        $btns.= \html_writer::end_tag('tr');
        $btns.= \html_writer::end_tag('table');

        return $btns;
    }

    abstract protected function get_action_button() : string ;

    private function get_back_to_overview_button() : string 
    {
        $attr = array(
            'type' => 'submit',
            'value' => get_string('back', 'coursework'),
            'form' => $this->backToOverviewFormName
        );
        $input = \html_writer::empty_tag('input', $attr);
        return \html_writer::tag('p', $input);
    }

    private function get_form_hidden_inputs() : string 
    {
        $attr = array(
            'type' => 'hidden',
            'name' => Main::ID,
            'value' => $this->cm->id
        );
        $inputs = \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::GUI_TYPE,
            'value' => Main::OVERVIEW
        );
        $inputs.= \html_writer::empty_tag('input', $attr);

        $inputs.= $this->get_unique_form_hidden_inputs();

        return $inputs;
    }

    abstract protected function get_unique_form_hidden_inputs() : string;

    private function get_html_form_end() : string 
    { 
        return \html_writer::end_tag('form'); 
    }

    private function get_back_to_overview_form() : string 
    {
        $attr = array(
            'id' => $this->backToOverviewFormName,
            'method' => 'post',
            'action' => Main::MODULE_URL
        );
        $btn = \html_writer::start_tag('form', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::ID,
            'value' => $this->cm->id
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::GUI_TYPE,
            'value' => Main::OVERVIEW
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $btn.= \html_writer::end_tag('form');

        return $btn;
    }


} 

