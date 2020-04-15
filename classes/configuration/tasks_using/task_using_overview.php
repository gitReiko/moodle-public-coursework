<?php


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

        $this->usingTask = $this->get_using_task();

        if(!empty($this->usingTask))
        {
            $this->taskSections = $this->get_task_sections();
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

    private function get_using_task()
    {
        global $DB;
        $sql = 'SELECT ct.*, ctu.id AS usingtaskid
                FROM {coursework_tasks} AS ct
                INNER JOIN {coursework_tasks_using} AS ctu
                ON ct.id = ctu.task
                WHERE coursework = ?';
        $conditions = array($this->cm->instance);
        return $DB->get_record_sql($sql, $conditions);
    }

    private function get_task_sections()
    {
        global $DB;
        $conditions = array('task' => $this->usingTask->id);
        return $DB->get_records('coursework_tasks_sections', $conditions, 'listposition, name');
    }

    private function get_overview_header() : string 
    {
        return '<h3>'.get_string('used_task_template', 'coursework').'</h3>';
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
        $button = '<form>';
        $button.= '<input type="submit" value="'.get_string('select_used_task_template', 'coursework').'">';
        $button.= '<input type="hidden" name="id" value="'.$this->cm->id.'" >';
        $button.= '<input type="hidden" name="'.CONFIG_MODULE.'" value="'.TASKS_USING.'">';
        $button.= '<input type="hidden" name="'.LeadersSetting::GUI_TYPE.'" value="'.TasksUsingMain::ADD_TASK_USING.'">';
        $button.= '</form>';
        return $button;
    }

    private function get_edit_task_using_button() : string 
    {
        $button = '<form>';
        $button.= '<input type="submit" value="'.get_string('select_used_task_template', 'coursework').'">';
        $button.= '<input type="hidden" name="id" value="'.$this->cm->id.'" >';
        $button.= '<input type="hidden" name="'.CONFIG_MODULE.'" value="'.TASKS_USING.'">';
        $button.= '<input type="hidden" name="'.LeadersSetting::GUI_TYPE.'" value="'.TasksUsingMain::EDIT_TASK_USING.'">';
        $button.= '<input type="hidden" name="'.TASK.ROW.ID.'" value="'.$this->usingTask->usingtaskid.'">';
        $button.= '</form>';
        return $button;
    }
    

}


