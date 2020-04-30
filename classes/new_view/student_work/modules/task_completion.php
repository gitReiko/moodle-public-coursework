<?php

use coursework_lib as lib;
use view_lib as view;

class TaskCompletion extends ViewModule 
{
    private $task;
    private $taskSections;

    function __construct(stdClass $course, stdClass $cm, int $studentId, bool $displayBlock = false)
    {
        parent::__construct($course, $cm, $studentId, $displayBlock);

        $this->task = $this->get_user_task();
        $this->taskSections = lib\get_task_sections($this->task->id);
    }

    protected function get_module_name() : string
    {
        return 'task_performance';
    }

    protected function get_module_header() : string
    {
        return get_string('task_performance', 'coursework');
    }

    protected function get_module_body() : string
    {
        if(view\is_user_have_task($this->cm, $this->studentId))
        {
            $body = $this->get_task_description();
            $body.= $this->get_task_sections_list();
        }
        else 
        {
            $body = $this->get_task_not_assigned_message();
        }

        return $body;
    }

    private function get_user_task() : stdClass 
    {
        global $DB;
        $taskId = $this->get_user_task_id();
        return $DB->get_record('coursework_tasks', array('id'=>$taskId));
    }

    private function get_user_task_id() : int 
    {
        global $DB;
        $where = array('coursework'=>$this->cm->instance, 'student'=>$this->studentId);
        return $DB->get_field('coursework_students', 'task', $where);
    }

    private function get_task_description() : string 
    {
        return '<div>'.$this->task->description.'</td>';
    }

    private function get_task_not_assigned_message() : string 
    {
        return '<p>'.get_string('task_not_assigned', 'coursework').'</p>';
    }

    private function get_task_sections_list() : string 
    {
        $table = '<h4>'.get_string('task_sections_list', 'coursework').'</h4>';
        $table.= '<table class="simple_table">';
        $table.= $this->get_task_sections_list_header();
        $table.= $this->get_task_sections_list_body();
        $table.= '</table>';
        return $table;
    }

    private function get_task_sections_list_header() : string 
    {
        $header = '<tr class="header">';
        $header.= '<td>'.get_string('name', 'coursework').'</td>';
        $header.= '<td>'.get_string('description', 'coursework'). '</td>';
        $header.= '<td>'.get_string('completion_date', 'coursework'). '</td>';
        $header.= '<td>'.get_string('state', 'coursework'). '</td>';
        $header.= '</tr>';
        return $header;
    }

    private function get_task_sections_list_body() : string 
    {
        $body = '';

        foreach($this->taskSections as $section)
        {
            $body.= '<tr>';
            $body.= '<td>'.$section->name.'</td>';
            $body.= '<td style="max-width: 450px;">'.$section->description.'</td>';

            if(empty($section->completiondate))
            {
                $body.= '<td></td>';
            }
            else
            {
                $body.= '<td>'.date('d-m-Y', $section->completiondate).'</td>';
            }
            
            $body.= '<td></td>';
            $body.= '</tr>';
        }

        return $body;
    }


}

