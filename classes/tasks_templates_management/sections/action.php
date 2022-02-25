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
    
    private function get_html_form_start() : string 
    {
        $attr = array('id' => self::ACTION_FORM, 'method' => 'post');
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
            'minlength' => 1,
            'maxlength' => 254,
            'size' => 80,
            'required' => 'required',
            'autofocus' => 'autofocus',
            'autocomplete' => 'off'
        );
        return \html_writer::empty_tag('input', $attr);
    }

    abstract protected function get_name_input_value() : string;

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

    private function list_position_field() : string 
    {
        $position = \html_writer::tag('h4', get_string('position_in_task_list', 'coursework'));
        $position.= \html_writer::tag('p', $this->get_list_position_input());
        return $position;
    }

    private function get_list_position_input() : string 
    {
        $attr = array(
            'type' => 'number',
            'name' => Main::LIST_POSITION,
            'value' => $this->get_list_position_input_value(),
            'min' => 1,
            'max' => 65530,
            'autocomplete' => 'off'
        );
        return \html_writer::empty_tag('input', $attr);
    }

    abstract protected function get_list_position_input_value() : string;

    private function get_completion_date_field() : string
    {
        $date = \html_writer::tag('h4', get_string('completion_date', 'coursework'));
        $date.= \html_writer::tag('p', $this->get_completion_date_input());
        return $date;
    }

    private function get_completion_date_input() : string 
    {
        $attr = array(
            'type' => 'date',
            'name' => Main::DEADLINE,
            'value' => $this->get_completion_date_value(),
            'autocomplete' => 'off'
        );
        return \html_writer::empty_tag('input', $attr);
    }

    abstract protected function get_completion_date_value() : string;

    private function get_buttons_panel() : string 
    {
        $attr = array('class' => 'btns_panel');
        $btns = \html_writer::start_tag('table');
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
        $params = \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::GUI_TYPE,
            'value' => Main::SECTIONS_MANAGEMENT
        );
        $params.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::TASK_ID,
            'value' => $this->task->id
        );
        $params.= \html_writer::empty_tag('input', $attr);

        $params.= $this->get_unique_form_hidden_inputs();

        return $params;
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
            'method' => 'post'
        );
        $form = \html_writer::start_tag('form', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::ID,
            'value' => $this->cm->id
        );
        $form.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::GUI_TYPE,
            'value' => Main::SECTIONS_MANAGEMENT
        );
        $form.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::TASK_ID,
            'value' => $this->task->id
        );
        $form.= \html_writer::empty_tag('input', $attr);

        $form.= \html_writer::end_tag('form');

        return $form;
    }


} 

