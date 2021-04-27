<?php

namespace Coursework\View\StudentWork\TaskAssignment;

use Coursework\Lib\Getters\CommonGetter as cg;

class CorrectTask extends AssignCustomTask 
{
    private $task;
    private $taskSections;

    function __construct(\stdClass $course, \stdClass $cm, int $studentId)
    {
        parent::__construct($course, $cm, $studentId);

        $this->task = cg::get_default_coursework_task($cm);

        if(empty($this->task->id))
        {
            throw new \Exception('Missing default coursework task id.');
        }

        $this->taskSections = cg::get_task_sections($this->task->id);
    }

    protected function get_page_header() : string
    {
        $attr = array('style' => 'font-size: large');
        $text = get_string('correct_task', 'coursework');
        return \html_writer::tag('p', $text, $attr);
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
            $cells = $this->get_name_cell($section);
            $cells.= $this->get_completion_date_cell($section);
            $cells.= $this->get_actions_buttons_cell();

            $attr = array(
                'id' => 'section'.$i,
                'class' => 'taskSections'
            );
            $tbody.= \html_writer::tag('tr', $cells, $attr);

            $i++;
        }

        return $tbody;
    }

    private function get_name_cell(\stdClass $section) : string 
    {
        $attr = array(
            'type' => 'text',
            'name' => 'name[]',
            'minlength' => 5,
            'maxlength' => 254,
            'required' => 'required',
            'size' => 80,
            'autocomplete' => 'off',
            'value' => $section->name,
            'form' => $this->formName
        );
        $input = \html_writer::empty_tag('input', $attr);

        return \html_writer::tag('td', $input);
    }

    private function get_completion_date_cell(\stdClass $section) : string 
    {
        $attr = array(
            'type' => 'date',
            'name' => 'completion_date[]',
            'class' => 'completion_date',
            'autocomplete' => 'off',
            'form' => $this->formName
        );

        if(!empty($section->completiondate))
        {
            $attr = array_merge($attr, array('value' => date('Y-m-d', $section->completiondate)));
        }

        $input = \html_writer::empty_tag('input', $attr);

        return \html_writer::tag('td', $input);
    }

    private function get_actions_buttons_cell() : string 
    {
        $attr = array('onclick' => 'CustomTaskPage.up_section(this);');
        $text = '↑';
        $btn = \html_writer::tag('button', $text, $attr);

        $attr = array('onclick' => 'CustomTaskPage.down_section(this);');
        $text = '↓';
        $btn.= \html_writer::tag('button', $text, $attr);

        $attr = array('onclick' => 'CustomTaskPage.delete_section(this);');
        $text = get_string('delete', 'coursework');
        $btn.= \html_writer::tag('button', $text, $attr);

        return \html_writer::tag('td', $btn);
    }



}