<?php

use task_templates_lib as locallib;

class TasksSectionsOverview
{
    private $course;
    private $cm;

    private $task;
    private $sections;

    function __construct(stdClass $course, stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->task = locallib\get_task_from_post();
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
        $header = '<h3>'.get_string('task_sections_list', 'coursework');
        $header.= ' <b>'.$this->task->name.'</b></h3>';
        return $header;
    }

    private function get_sections_table() : string 
    {
        $table = '<table class="leaders_overview">';
        $table.= $this->get_sections_table_header();
        $table.= $this->get_sections_table_body();
        $table.= '</table>';
        return $table;
    }

    private function get_sections_table_header() : string 
    {
        $header = '<tr class="header">';
        $header.= '<td>'.get_string('name', 'coursework').'</td>';
        $header.= '<td>'.get_string('description', 'coursework'). '</td>';
        $header.= '<td>'.get_string('completion_date', 'coursework'). '</td>';
        $header.= '<td></td>';
        $header.= '</tr>';
        return $header;
    }

    private function get_sections_table_body() : string 
    {
        $body = '';

        foreach($this->sections as $section)
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
            
            $body.= '<td>'.$this->get_edit_button($section).'</td>';
            $body.= '</tr>';
        }

        return $body;
    }

    private function get_edit_button(stdClass $section) : string 
    {
        $button = '<form method="post">';
        $button.= '<input type="submit" value="'.get_string('edit', 'coursework').'">';
        $button.= '<input type="hidden" name="id" value="'.$this->cm->id.'" >';
        $button.= '<input type="hidden" name="'.CONFIG_MODULE.'" value="'.TASKS_TEMPLATES_MANAGEMENT.'">';
        $button.= '<input type="hidden" name="'.LeadersSetting::GUI_TYPE.'" value="'.TasksManagement::EDIT_SECTION.'">';
        $button.= '<input type="hidden" name="'.TASK.ID.'" value="'.$this->task->id.'">';
        $button.= '<input type="hidden" name="'.SECTION.ID.'" value="'.$section->id.'">';
        $button.= '</form>';
        return $button;
    }

    private function get_buttons_panel() : string 
    {
        $btns = '<table class="btns_panel"><tr>';
        $btns.= '<td>'.$this->get_add_task_section_button().'</td>';
        $btns.= '<td>'.$this->get_back_to_overview_button().'</td>';
        $btns.= '</tr></table>';
        return $btns;
    }

    private function get_add_task_section_button() : string 
    {
        $button = '<form method="post">';
        $button.= '<input type="submit" value="'.get_string('add_task_section', 'coursework').'" autofocus>';
        $button.= '<input type="hidden" name="id" value="'.$this->cm->id.'" >';
        $button.= '<input type="hidden" name="'.CONFIG_MODULE.'" value="'.TASKS_TEMPLATES_MANAGEMENT.'">';
        $button.= '<input type="hidden" name="'.LeadersSetting::GUI_TYPE.'" value="'.TasksManagement::ADD_SECTION.'">';
        $button.= '<input type="hidden" name="'.TASK.ID.'" value="'.$this->task->id.'">';
        $button.= '</form>';
        return $button;
    }

    private function get_back_to_overview_button() : string 
    {
        $button = '<form method="post">';
        $button.= '<input type="hidden" name="id" value="'.$this->cm->id.'" >';
        $button.= '<input type="hidden" name="'.CONFIG_MODULE.'" value="'.TASKS_TEMPLATES_MANAGEMENT.'">';
        $button.= '<input type="hidden" name="'.LeadersSetting::GUI_TYPE.'" value="'.TasksManagement::OVERVIEW.'">';
        $button.= '<input type="submit" value="'.get_string('back', 'coursework').'" >';
        $button.= '</form>';
        return $button;
    }
    

}


