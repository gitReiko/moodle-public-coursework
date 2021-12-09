<?php

use coursework_lib as lib;

class TasksUsingOverview
{
    private $course;
    private $cm;

    private $usingTask;
    private $taskSections;

    function __construct(stdClass $course, stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->usingTask = lib\get_using_task($this->cm);

        if(!empty($this->usingTask))
        {
            $this->taskSections = lib\get_task_sections($this->usingTask->id);
        }
    }

    public function get_gui() : string 
    {
        $gui = $this->get_overview_header();

        if(empty($this->usingTask))
        {
            $gui.= $this->get_add_task_using_button();
        }
        else
        {
            $gui.= $this->get_edit_task_using_button();
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
        return '<h3>'.get_string('default_task', 'coursework').'</h3>';
    }

    private function get_using_task_info() : string 
    {
        $info = '<h4><b>'.$this->usingTask->name.'</b></h4>';
        $info.= '<p>'.$this->usingTask->description.'</p>';
        return $info;
    }

    private function get_task_sections_list() : string 
    {
        $table = '<h4>'.get_string('task_sections_list', 'coursework').'</h4>';
        $table.= '<table class="leaders_overview">';
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

    private function get_sections_not_created() : string 
    {
        return '<p>'.get_string('task_sections_not_created', 'coursework').'</p>';
    }

    private function get_add_task_using_button() : string 
    {
        $button = '<form method="post">';
        $button.= '<input type="submit" value="'.get_string('select_default_task', 'coursework').'" autofocus>';
        $button.= '<input type="hidden" name="id" value="'.$this->cm->id.'" >';
        $button.= '<input type="hidden" name="'.CONFIG_MODULE.'" value="'.TASKS_USING.'">';
        $button.= '<input type="hidden" name="'.LeadersSetting::GUI_TYPE.'" value="'.TasksUsingMain::ADD_DEFAULT_TASK.'">';
        $button.= '</form>';
        return $button;
    }

    private function get_edit_task_using_button() : string 
    {
        $button = '<form method="post">';
        $button.= '<input type="submit" value="'.get_string('select_default_task', 'coursework').'" autofocus>';
        $button.= '<input type="hidden" name="id" value="'.$this->cm->id.'" >';
        $button.= '<input type="hidden" name="'.CONFIG_MODULE.'" value="'.TASKS_USING.'">';
        $button.= '<input type="hidden" name="'.LeadersSetting::GUI_TYPE.'" value="'.TasksUsingMain::EDIT_TASK_USING.'">';
        $button.= '<input type="hidden" name="'.TASK.ROW.ID.'" value="'.$this->usingTask->usingtaskid.'">';
        $button.= '</form>';
        return $button;
    }
    

}


