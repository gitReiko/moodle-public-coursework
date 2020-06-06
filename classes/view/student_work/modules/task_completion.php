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

        $this->task = lib\get_user_task($this->cm, $this->studentId);
        $this->taskSections = $this->add_status_to_task_sections();
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

    private function add_status_to_task_sections()
    {
        $sections = lib\get_task_sections($this->task->id);

        foreach($sections as $section)
        {
            if(lib\is_section_status_exist($this->cm, $this->studentId, $section->id))
            {
                $state = lib\get_student_section_status($this->cm, $this->studentId, $section->id);
                $section->status = $state->status;
                $section->statusmodified = $state->timemodified;
            }
            else 
            {
                $section->status = NOT_READY;
                $section->statusmodified = 0;
            }
        }

        return $sections;
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
        $header.= '<td>'.get_string('need_to_complete_before', 'coursework'). '</td>';
        $header.= '<td>'.get_string('state', 'coursework'). '</td>';
        $header.= '<td>'.get_string('status_change_date', 'coursework'). '</td>';
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
            $body.= '<td>'.$this->get_formatted_date($section->completiondate).'</td>';
            $body.= $this->get_status_field($section);
            $body.= '<td>'.$this->get_formatted_date($section->statusmodified).'</td>';
            $body.= '</tr>';
        }

        return $body;
    }

    private function get_formatted_date($date) : string 
    {
        $formattedDate = '';

        if(!empty($date))
        {
            $formattedDate.= date('d-m-Y', $date);
        }

        return $formattedDate;
    }

    private function get_status_field(stdClass $section) : string 
    {
        $field = '';

        if(!empty($section->completiondate))
        {
            switch($section->status) 
            {
                case NOT_READY:
                    $field.= '<td class="red-background">';
                    break;
                case READY:
                    $field.= '<td class="green-background">';
                    break;
                case NEED_TO_FIX:
                    $field.= '<td class="yellow-background">';
                    break;
                case SENT_TO_CHECK:
                    $field.= '<td class="blue-background">';
                    break;
            }
            $field.= get_string($section->status, 'coursework');
            $field.= '</td>';
        }
        else 
        {
            $field.= '<td></td>';
        }

        return $field;
    }


}

