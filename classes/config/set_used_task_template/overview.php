<?php

namespace Coursework\Config\SetUsedTaskTemplate;

use Coursework\Lib\Getters\CommonGetter as cg;

class Overview
{
    private $course;
    private $cm;

    private $usingTask;
    private $taskSections;

    function __construct(\stdClass $course, \stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->usingTask = cg::get_default_coursework_task($this->cm);

        if(!empty($this->usingTask))
        {
            $this->taskSections = cg::get_task_sections($this->usingTask->id);
        }
    }

    public function get_gui() : string 
    {
        $gui = $this->get_overview_header();

        $gui.= $this->get_action_button();

        if(!empty($this->usingTask))
        {
            $gui.= $this->get_using_task_info();

            if(count($this->taskSections))
            {
                $gui.= $this->get_task_sections_list();
            }
            else
            {
                $gui.= $this->get_sections_not_created();
            }
        }

        return $gui;
    }

    private function get_overview_header() : string 
    {
        $text = get_string('used_task_template', 'coursework');
        return \html_writer::tag('h3', $text);
    }

    private function get_using_task_info() : string 
    {
        $text = \html_writer::tag('b', $this->usingTask->name);
        $info = \html_writer::tag('h4', $text);

        $text = $this->usingTask->description;
        $info.= \html_writer::tag('p', $text);

        return $info;
    }

    private function get_task_sections_list() : string 
    {
        $text = get_string('task_sections_list', 'coursework');
        $list = \html_writer::tag('h4', $text);

        $attr = array('class' => 'leaders_overview');
        $list.= \html_writer::start_tag('table', $attr);

        $list.= $this->get_task_sections_list_header();
        $list.= $this->get_task_sections_list_body();

        $list.= \html_writer::end_tag('table');

        return $list;
    }

    private function get_task_sections_list_header() : string 
    {
        $attr = array('class' => 'header');
        $head = \html_writer::start_tag('tr', $attr);
        $head.= \html_writer::tag('td', get_string('name', 'coursework'));
        $head.= \html_writer::tag('td', get_string('description', 'coursework'));
        $head.= \html_writer::tag('td', get_string('completion_date', 'coursework'));
        $head = \html_writer::end_tag('tr');

        return $head;
    }

    private function get_task_sections_list_body() : string 
    {
        $body = '';

        foreach($this->taskSections as $section)
        {
            $body.= \html_writer::start_tag('tr');
            $body.= \html_writer::tag('td', $section->name);
            $attr = array('style' => 'max-width: 450px;');
            $body.= \html_writer::tag('td', $section->description, $attr);

            if(empty($section->completiondate))
            {
                $body.= \html_writer::tag('td', '');
            }
            else
            {
                $text = date('d-m-Y', $section->completiondate);
                $body.= \html_writer::tag('td', $text);
            }
            
            $body.= \html_writer::end_tag('td');
        }

        return $body;
    }

    private function get_sections_not_created() : string 
    {
        $text = get_string('task_sections_not_created', 'coursework');
        return \html_writer::tag('h3', $text);
    }

    private function get_action_button() : string 
    {
        $attr = array('method' => 'post');
        $btn = \html_writer::start_tag('form', $attr);

        $attr = array(
            'type' => 'submit',
            'value' => get_string('select_used_task_template', 'coursework'),
            'autofocus' => 'autofocus'
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::ID,
            'value' => $this->cm->id
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        if(empty($this->usingTask))
        {
            $btn.= $this->get_add_event_input();
        }
        else 
        {
            $btn.= $this->get_edit_event_inputs();
        }

        $btn.= \html_writer::end_tag('form');

        return $btn;
    }

    private function get_add_event_input() : string 
    {
        $attr = array(
            'type' => 'hidden',
            'name' => Main::GUI_TYPE,
            'value' => Main::ADD_TASK_USING
        );
        return \html_writer::empty_tag('input', $attr);
    }

    private function get_edit_event_inputs() : string 
    {
        $attr = array(
            'type' => 'hidden',
            'name' => Main::GUI_TYPE,
            'value' => Main::EDIT_TASK_USING
        );
        $btn = \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::TASK_ROW_ID,
            'value' => $this->usingTask->rowid
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        return $btn;
    }
    

}


