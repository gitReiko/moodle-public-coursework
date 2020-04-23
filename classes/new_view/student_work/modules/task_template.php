<?php

use coursework_lib as lib;

class TaskTemplate extends ViewModule 
{

    private $task;
    private $taskSections;

    function __construct(stdClass $course, stdClass $cm, int $studentId, bool $displayBlock = false)
    {
        parent::__construct($course, $cm, $studentId, $displayBlock);

        $this->task = lib\get_using_task($this->cm);
        $this->taskSections = lib\get_task_sections($this->task->id);
    }



    protected function get_module_name() : string
    {
        return 'task';
    }

    protected function get_module_header() : string
    {
        return get_string('task_template', 'coursework');
    }

    protected function get_module_body() : string
    {
        $body = '';
        if($this->is_task_template_assigned())
        {
            $body.= $this->get_it_is_only_task_template();
            $body.= $this->get_task_info();
            $body.= $this->get_task_sections_list();
        }
        else 
        {
            $body.= $this->get_task_template_not_assigned();
        }

        return $body;
    }

    private function is_task_template_assigned() : bool 
    {
        if(empty($this->task->id)) return false;
        else return true;
    }

    private function get_it_is_only_task_template() : string 
    {
        return '<p class="red"><b>'.get_string('it_is_only_template', 'coursework').'</b></p>';
    }

    private function get_task_template_not_assigned() : string 
    {
        return '<p>'.get_string('task_template_not_assigned', 'coursework').'</p>';
    }

    private function get_task_info() : string 
    {
        $info = '<h4><b>'.$this->task->name.'</b></h4>';
        $info.= '<p>'.$this->task->description.'</p>';
        return $info;
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
            
            $body.= '</tr>';
        }

        return $body;
    }



}

