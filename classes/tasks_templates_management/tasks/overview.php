<?php

namespace Coursework\View\TasksTemplatesManagement\Tasks;

use Coursework\View\TasksTemplatesManagement\Main;

class Overview
{
    private $course;
    private $cm;

    private $tasks;

    function __construct(\stdClass $course, \stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->tasks = $this->get_task_templates();
    }

    public function get_gui() : string 
    {
        $gui = $this->get_overview_header();

        if(count($this->tasks))
        {
            $gui.= $this->get_tasks_table();
        }

        $gui.= $this->get_add_task_template_button();

        return $gui;
    }

    private function get_task_templates()
    {
        global $DB;
        return $DB->get_records('coursework_tasks', array('template' => 1), 'name');
    }

    private function get_overview_header() : string 
    {
        return \html_writer::tag('h3', get_string('tasks_templates_list', 'coursework'));
    }


    private function get_tasks_table() : string 
    {
        $attr = array('class' => 'leaders_overview');
        $table = \html_writer::start_tag('table', $attr);
        $table.= $this->get_tasks_table_header();
        $table.= $this->get_tasks_table_body();
        $table.= \html_writer::end_tag('table');

        return $table;
    }

    private function get_tasks_table_header() : string 
    {
        $attr = array('class' => 'header');
        $head = \html_writer::start_tag('tr', $attr);
        $head.= \html_writer::tag('td', get_string('name', 'coursework'));
        $head.= \html_writer::tag('td', get_string('description', 'coursework'));
        $head.= \html_writer::tag('td', '');
        $head.= \html_writer::tag('td', '');
        $head.= \html_writer::end_tag('tr', '');

        return $head;
    }

    private function get_tasks_table_body() : string 
    {
        $body = '';

        foreach($this->tasks as $task)
        {
            $body.= \html_writer::start_tag('tr');
            $body.= \html_writer::tag('td', $task->name);
            $attr = array('style' => 'max-width: 450px;');
            $body.= \html_writer::tag('td', $task->description, $attr);
            $body.= \html_writer::tag('td', $this->get_edit_button($task));
            $body.= \html_writer::tag('td', $this->get_sections_management_button($task));
            $body.= \html_writer::end_tag('tr');
        }

        return $body;
    }

    private function get_edit_button(\stdClass $task) : string 
    {
        $attr = array(
            'method' => 'post',
            'action' => Main::MODULE_URL
        );
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
            'value' => Main::EDIT_TASK
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::TASK_ID,
            'value' => $task->id
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $btn.= \html_writer::end_tag('form');

        return $btn;
    }

    private function get_sections_management_button(\stdClass $task) : string 
    {
        $attr = array(
            'method' => 'post',
            'action' => Main::MODULE_URL
        );
        $btn = \html_writer::start_tag('form', $attr);

        $attr = array(
            'type' => 'submit',
            'value' => get_string('task_sections_management', 'coursework')
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
            'value' => Main::SECTIONS_MANAGEMENT
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::TASK_ID,
            'value' => $task->id
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $btn.= \html_writer::end_tag('form');

        return $btn;
    }

    private function get_add_task_template_button() : string 
    {
        $attr = array(
            'method' => 'post',
            'action' => Main::MODULE_URL
        );
        $btn = \html_writer::start_tag('form', $attr);

        $attr = array(
            'type' => 'submit',
            'value' => get_string('add_task_template', 'coursework')
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
            'value' => Main::ADD_TASK
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $btn.= \html_writer::end_tag('form');

        return $btn;
    }
    

}


