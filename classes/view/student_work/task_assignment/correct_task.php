<?php

use coursework_lib as lib;
use view_lib as view;

class CorrectTask extends AssignCustomTask 
{
    private $task;
    private $taskSections;

    function __construct(stdClass $course, stdClass $cm, int $studentId)
    {
        parent::__construct($course, $cm, $studentId);

        $this->task = lib\get_using_task($cm);
        $this->taskSections = lib\get_task_sections($this->task->id);
    }

    protected function get_page_header() : string
    {
        return '<h3>'.get_string('correct_task', 'coursework').'</h3>';
    }

    protected function get_description_value() : string
    {
        return $this->task->description;
    }

    protected function get_tbody_sections() : string
    {
        $tbody = '';
        $i = 0;
        foreach($this->taskSections as $section)
        {
            $tbody.= '<tr id="section'.$i.'" class="taskSections">';
            $tbody.= $this->get_name_cell($section);
            $tbody.= $this->get_completion_date_cell($section);
            $tbody.= $this->get_actions_buttons_cell();
            $tbody.= '</tr>';
            $i++;
        }
        return $tbody;
    }

    private function get_name_cell(stdClass $section) : string 
    {
        $cell = '<td>';
        $cell.= '<input type="text" name="name[]" ';
        $cell.= ' minlength="5" maxlength="254" required ';
        $cell.= ' size="80" autocomplete="off" ';
        $cell.= ' value="'.$section->name.'" ';
        $cell.= ' form="'.$this->formName.'" >';
        $cell.= '</td>';
        return $cell;
    }

    private function get_completion_date_cell(stdClass $section) : string 
    {
        $cell = '<td>';
        $cell.= '<input type="date" name="completion_date[]" ';
        $cell.= ' class="completion_date" autocomplete="off" ';
        if(!empty($section->completiondate))
        {
            $cell.= ' value="'.date('Y-m-d', $section->completiondate).'" ';
        }
        $cell.= ' form="'.$this->formName.'" >';
        $cell.= '</td>';
        return $cell;
    }

    private function get_actions_buttons_cell() : string 
    {
        $cell = '<td>';
        $cell.= '<button onclick="CustomTaskPage.up_section(this);">↑</button>';
        $cell.= '<button onclick="CustomTaskPage.down_section(this);">↓</button>';
        $cell.= '<button onclick="CustomTaskPage.delete_section(this);">';
        $cell.= get_string('delete', 'coursework').'</button>';
        $cell.= '</td>';
        return $cell;
    }



}