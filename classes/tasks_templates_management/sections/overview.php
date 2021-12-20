<?php

namespace Coursework\View\TasksTemplatesManagement\Sections;

use Coursework\View\TasksTemplatesManagement\Main;
use Coursework\View\TasksTemplatesManagement\Lib;

class Overview
{
    private $course;
    private $cm;

    private $task;
    private $sections;

    function __construct(\stdClass $course, \stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->task = Lib::get_task_from_post();
        $this->sections = $this->get_sections();
    }

    public function get_gui() : string 
    {
        $gui = $this->get_overview_header();

        if(count($this->sections))
        {
            $gui.= $this->get_sections_table();
        }

        $gui.= $this->get_buttons_panel();

        return $gui;
    }

    private function get_sections()
    {
        global $DB;
        $conditions = array('task' => $this->task->id);
        return $DB->get_records('coursework_tasks_sections', $conditions, 'listposition, name');
    }

    private function get_overview_header() : string 
    {
        $text = get_string('task_sections_list', 'coursework').' ';
        $text.= \html_writer::tag('b', $this->task->name);
        return \html_writer::tag('h3', $text);
    }

    private function get_sections_table() : string 
    {
        $attr = array('class' => 'leaders_overview');
        $table = \html_writer::start_tag('table', $attr);
        $table.= $this->get_sections_table_header();
        $table.= $this->get_sections_table_body();
        $table.= \html_writer::end_tag('table');

        return $table;
    }

    private function get_sections_table_header() : string 
    {
        $attr = array('class' => 'header');
        $head = \html_writer::start_tag('tr', $attr);
        $head.= \html_writer::tag('td', get_string('name', 'coursework'));
        $head.= \html_writer::tag('td', get_string('description', 'coursework'));
        $head.= \html_writer::tag('td', get_string('completion_date', 'coursework'));
        $head.= \html_writer::tag('td', '');
        $head.= \html_writer::end_tag('tr');

        return $head;
    }

    private function get_sections_table_body() : string 
    {
        $body = '';

        foreach($this->sections as $section)
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

            $body.= \html_writer::tag('td', $this->get_edit_button($section));
            $body.= \html_writer::end_tag('tr');
        }

        return $body;
    }

    private function get_edit_button(\stdClass $section) : string 
    {
        $attr = array('method' => 'post');
        $btn = \html_writer::start_tag('form', $attr);

        $attr = array(
            'type' => 'submit',
            'value' => get_string('edit', 'coursework')
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
            'value' => Main::EDIT_SECTION
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::TASK_ID,
            'value' => $this->task->id
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::SECTION_ID,
            'value' => $section->id
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $btn.= \html_writer::end_tag('form');

        return $btn;
    }

    private function get_buttons_panel() : string 
    {
        $attr = array('class' => 'btns_panel');
        $btns = \html_writer::start_tag('table', $attr);
        $btns.= \html_writer::start_tag('tr');
        $btns.= \html_writer::tag('td', $this->get_add_task_section_button());
        $btns.= \html_writer::tag('td', $this->get_back_to_overview_button());
        $btns.= \html_writer::end_tag('tr');
        $btns.= \html_writer::end_tag('table');

        return $btns;
    }

    private function get_add_task_section_button() : string 
    {
        $attr = array('method' => 'post');
        $btn = \html_writer::start_tag('form', $attr);

        $attr = array(
            'type' => 'submit',
            'value' => get_string('add_task_section', 'coursework')
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
            'value' => Main::ADD_SECTION
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::TASK_ID,
            'value' => $this->task->id
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $btn.= \html_writer::end_tag('form');

        return $btn;
    }

    private function get_back_to_overview_button() : string 
    {
        $attr = array('method' => 'post');
        $btn = \html_writer::start_tag('form', $attr);

        $attr = array(
            'type' => 'submit',
            'value' => get_string('back', 'coursework')
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
            'value' => Main::OVERVIEW
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $btn.= \html_writer::end_tag('form');

        return $btn;
    }
    

}


