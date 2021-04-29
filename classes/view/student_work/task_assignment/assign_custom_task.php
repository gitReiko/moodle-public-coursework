<?php

namespace Coursework\View\StudentWork\TaskAssignment;

use Coursework\View\Main as view_main;

abstract class AssignCustomTask 
{
    protected $course;
    protected $cm;
    protected $studentId;
    protected $formName = 'custom_form';

    function __construct(\stdClass $course, \stdClass $cm, int $studentId)
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
        $attr = array('style' => 'font-size:large');
        $text = get_string('description', 'coursework');
        $str = \html_writer::tag('h3', $text, $attr);

        $text = $this->get_description_textarea();
        $str.= \html_writer::tag('p', $text);

        return $str;
    }

    private function get_description_textarea() : string 
    {
        $attr = array(
            'name' => DESCRIPTION,
            'cols' => 80,
            'rows' => 5,
            'form' => $this->formName,
            'autofocus' => 'autofocus'
        );
        $text = $this->get_description_value();
        return \html_writer::tag('textarea', $text, $attr);
    }

    abstract protected function get_description_value() : string;

    private function get_task_sections_list() : string 
    {
        $attr = array('style' => 'font-size: large');
        $text = get_string('task_sections_list', 'coursework');
        $str = \html_writer::tag('p', $text, $attr);

        $attr = array('class' => 'simple_table');
        $text = $this->get_task_sections_list_header();
        $text.= $this->get_task_sections_list_body();
        return \html_writer::tag('table', $text, $attr);
    }

    private function get_task_sections_list_header() : string 
    {
        $text = get_string('name', 'coursework');
        $header = \html_writer::tag('td', $text);

        $text = get_string('completion_date', 'coursework');
        $header.= \html_writer::tag('td', $text);

        $text = '';
        $header.= \html_writer::tag('td', $text);

        $attr = array('class' => 'header');
        $header = \html_writer::tag('tr', $header, $attr);

        $header = \html_writer::tag('thead', $header);

        return $header;
    }

    private function get_task_sections_list_body() : string 
    {
        $attr = array('id' => 'sections_container');
        $text = $this->get_tbody_sections();
        return \html_writer::tag('tbody', $text, $attr);
    }

    abstract protected function get_tbody_sections() : string;

    private function get_add_section_button() : string 
    {
        $attr = array('onclick' => 'CustomTaskPage.add_section()');
        $text = get_string('add_task_section', 'coursework');
        $btn = \html_writer::tag('button', $text, $attr);

        return \html_writer::tag('p', $btn);
    }

    private function get_button_block() : string 
    {
        $text = $this->get_give_task_button();
        $block = \html_writer::tag('td', $text);

        $text = $this->get_back_button();
        $block.= \html_writer::tag('td', $text);

        $block = \html_writer::tag('tr', $block);
        
        return \html_writer::tag('table', $block);
    }

    private function get_give_task_button() : string 
    {
        $attr = array(
            'onclick' => 'return CustomTaskPage.validate_form()',
            'form' => $this->formName
        );
        $text = get_string('give_task', 'coursework');
        return \html_writer::tag('button', $text, $attr);
    }

    private function get_back_button() : string 
    {
        $attr = array(
            'type' => 'hidden',
            'name' => ID,
            'value' => $this->cm->id
        );
        $btn = \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => view_main::GUI_EVENT,
            'value' => view_main::USER_WORK
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => STUDENT.ID,
            'value' => $this->studentId
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $text = get_string('back', 'coursework');
        $btn.= \html_writer::tag('button', $text);

        $attr = array(
            'method' => 'post',
            'style' => 'display:inline-block'
        );
        return \html_writer::tag('form', $btn, $attr);
    }

    private function get_custom_assignment_form() : string 
    {
        $attr = array(
            'type' => 'hidden',
            'name' => ID,
            'value' => $this->cm->id
        );
        $form = \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => STUDENT.ID,
            'value' => $this->studentId
        );
        $form.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => DB_EVENT,
            'value' => \ViewDatabaseHandler::CUSTOM_TASK_ASSIGNMENT
        );
        $form.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'id' => $this->formName,
            'method' => 'post',
            'style' => 'display:inline-block'
        );
        return \html_writer::tag('form', $form, $attr);
    }


}