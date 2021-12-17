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
        return '<h3>'.get_string('tasks_templates_list', 'coursework').'</h3>';
    }


    private function get_tasks_table() : string 
    {
        $table = '<table class="leaders_overview">';
        $table.= $this->get_tasks_table_header();
        $table.= $this->get_tasks_table_body();
        $table.= '</table>';
        return $table;
    }

    private function get_tasks_table_header() : string 
    {
        $header = '<tr class="header">';
        $header.= '<td>'.get_string('name', 'coursework').'</td>';
        $header.= '<td>'.get_string('description', 'coursework'). '</td>';
        $header.= '<td></td>';
        $header.= '<td></td>';
        $header.= '</tr>';
        return $header;
    }

    private function get_tasks_table_body() : string 
    {
        $body = '';

        foreach($this->tasks as $task)
        {
            $body.= '<tr>';
            $body.= '<td>'.$task->name.'</td>';
            $body.= '<td style="max-width: 450px;">'.$task->description.'</td>';
            $body.= '<td>'.$this->get_edit_button($task).'</td>';
            $body.= '<td>'.$this->get_sections_management_button($task).'</td>';
            $body.= '</tr>';
        }

        return $body;
    }

    private function get_edit_button(\stdClass $task) : string 
    {
        $button = '<form method="post">';
        $button.= '<input type="submit" value="'.get_string('edit', 'coursework').'">';
        $button.= '<input type="hidden" name="'.Main::ID.'" value="'.$this->cm->id.'" >';
        $button.= '<input type="hidden" name="'.Main::GUI_TYPE.'" value="'.Main::EDIT_TASK.'">';
        $button.= '<input type="hidden" name="'.Main::TASK_ID.'" value="'.$task->id.'">';
        $button.= '</form>';
        return $button;
    }

    private function get_sections_management_button(\stdClass $task) : string 
    {
        $button = '<form method="post">';
        $button.= '<input type="submit" value="'.get_string('task_sections_management', 'coursework').'">';
        $button.= '<input type="hidden" name="'.Main::ID.'" value="'.$this->cm->id.'" >';
        $button.= '<input type="hidden" name="'.Main::GUI_TYPE.'" value="'.Main::SECTIONS_MANAGEMENT.'">';
        $button.= '<input type="hidden" name="'.Main::TASK_ID.'" value="'.$task->id.'">';
        $button.= '</form>';
        return $button;
    }

    private function get_add_task_template_button() : string 
    {
        $button = '<form method="post">';
        $button.= '<input type="submit" value="'.get_string('add_task_template', 'coursework').'" autofocus>';
        $button.= '<input type="hidden" name="'.Main::ID.'" value="'.$this->cm->id.'" >';
        $button.= '<input type="hidden" name="'.Main::GUI_TYPE.'" value="'.Main::ADD_TASK.'">';
        $button.= '</form>';
        return $button;
    }
    

}


