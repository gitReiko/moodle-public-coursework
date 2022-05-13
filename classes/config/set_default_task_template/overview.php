<?php

namespace Coursework\Config\SetDefaultTaskTemplate;

use Coursework\Lib\Getters\CommonGetter as cg;

class Overview
{
    private $course;
    private $cm;

    private $defaultTask;
    private $taskSections;

    function __construct(\stdClass $course, \stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->defaultTask = cg::get_default_coursework_task($this->cm);

        if(!empty($this->defaultTask))
        {
            $this->taskSections = cg::get_task_sections($this->defaultTask->id);
        }
    }

    public function get_gui() : string 
    {
        $gui = $this->get_overview_header();

        $gui.= $this->get_action_button();

        if(!empty($this->defaultTask))
        {
            $gui.= $this->get_default_task_info();

            if(count($this->taskSections))
            {
                $gui.= $this->get_task_sections_list();
            }
            else
            {
                $gui.= $this->get_sections_not_created();
            }
        }
        else 
        {
            $text = get_string('task_template_not_using', 'coursework');
            $gui.= \html_writer::tag('p', $text);
        }

        return $gui;
    }

    private function get_overview_header() : string 
    {
        $text = get_string('default_task', 'coursework');
        return \html_writer::tag('h3', $text);
    }

    private function get_default_task_info() : string 
    {
        $text = \html_writer::tag('b', $this->defaultTask->name);
        $info = \html_writer::tag('h4', $text);

        $text = $this->defaultTask->description;
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
        $head = \html_writer::start_tag('thead');
        $head.= \html_writer::start_tag('tr');
        $head.= \html_writer::tag('td', get_string('name', 'coursework'));
        $head.= \html_writer::tag('td', get_string('description', 'coursework'));
        $head.= \html_writer::tag('td', get_string('completion_date', 'coursework'));
        $head.= \html_writer::end_tag('tr');
        $head.= \html_writer::end_tag('thead');

        return $head;
    }

    private function get_task_sections_list_body() : string 
    {
        $body = \html_writer::start_tag('tbody');

        foreach($this->taskSections as $section)
        {
            $body.= \html_writer::start_tag('tr');
            $body.= \html_writer::tag('td', $section->name);
            $attr = array('style' => 'max-width: 450px;');
            $body.= \html_writer::tag('td', $section->description, $attr);

            if(empty($section->deadline))
            {
                $body.= \html_writer::tag('td', '');
            }
            else
            {
                $text = date('d-m-Y', $section->deadline);
                $body.= \html_writer::tag('td', $text);
            }
            
            $body.= \html_writer::end_tag('td');
        }

        $body.= \html_writer::end_tag('tbody');

        return $body;
    }

    private function get_sections_not_created() : string 
    {
        $text = get_string('task_sections_not_created', 'coursework');
        return \html_writer::tag('h3', $text);
    }

    private function get_action_button() : string 
    {
        $attr = array(
            'method' => 'post',
            'action' => Main::MODULE_URL
        );
        $btn = \html_writer::start_tag('form', $attr);

        $attr = array(
            'type' => 'submit',
            'value' => get_string('select_default_task', 'coursework'),
            'autofocus' => 'autofocus'
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::ID,
            'value' => $this->cm->id
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::GUI_TYPE,
            'value' => Main::SET_DEFAULT_TASK
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $btn.= \html_writer::end_tag('form');

        return $btn;
    }    

}


