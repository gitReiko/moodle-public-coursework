<?php

namespace Coursework\View\StudentWork\TaskAssignment;

use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\View\Main as view_main;

class TaskAssignMethods 
{
    private $cm;
    private $studentId;
    private $task;
    private $taskSections;

    function __construct(\stdClass $cm, int $studentId)
    {
        $this->cm = $cm;
        $this->studentId = $studentId;

        $this->task = cg::get_default_coursework_task($cm);

        if($this->is_default_task_exists())
        {
            $this->taskSections = cg::get_task_sections($this->task->id);
        }
    }

    public function get()
    {
        $str = $this->get_header();

        if($this->is_default_task_exists())
        {
            $str.= $this->get_default_task();
        }
        
        $str.= $this->get_methods_buttons();

        return $str;
    }

    private function is_default_task_exists() : bool 
    {
        if(empty($this->task->id))
        {
            return false;
        }
        else 
        {
            return true;
        }
    }

    private function get_header() : string 
    {
        $attr = array('style' => 'font-size:large;');
        $text = get_string('issuing_assignment_to_student', 'coursework');
        return \html_writer::tag('p', $text, $attr);
    }

    private function get_default_task() : string 
    {
        $str = \html_writer::empty_tag('hr');
        $str.= $this->get_default_task_header();
        $str.= $this->get_default_task_sections();
        $str.= \html_writer::empty_tag('hr');

        return $str;
    }

    private function get_default_task_header() : string 
    {
        $text = get_string('default_task', 'coursework');
        return \html_writer::tag('p', $text);
    }

    private function get_default_task_sections() : string 
    {
        $str = '';

        foreach($this->taskSections as $section)
        {
            $text = $section->name;
            $str.= \html_writer::tag('li', $text);
        }

        return \html_writer::tag('ol', $str);
    }

    private function get_methods_buttons() : string 
    {
        $text = '';

        if($this->is_default_task_exists())
        {
            $text.= $this->get_use_template_button();
            $text.= $this->get_correct_template_button();
        }
        
        $text.= $this->get_create_task_button();

        return \html_writer::tag('p', $text);
    }

    private function get_use_template_button() : string 
    {
        $btn = $this->get_neccessary_form_params();

        $attr = array(
            'type' => 'hidden',
            'name' => DB_EVENT,
            'value' => \ViewDatabaseHandler::USE_TASK_TEMPLATE
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $text = get_string('assign_default_task', 'coursework');
        $btn.= \html_writer::tag('button', $text);

        $attr = array(
            'method' => 'post',
            'style' => 'display: inline-block'
        );
        return \html_writer::tag('form', $btn, $attr);
    }

    private function get_neccessary_form_params() : string 
    {
        $attr = array(
            'type' => 'hidden',
            'name' => ID,
            'value' => $this->cm->id
        );
        $params = \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => STUDENT.ID,
            'value' => $this->studentId
        );
        $params.= \html_writer::empty_tag('input', $attr);

        return $params;
    }

    private function get_correct_template_button() : string 
    {
        $btn = $this->get_neccessary_form_params();

        $attr = array(
            'type' => 'hidden', 
            'name' => view_main::GUI_EVENT, 
            'value' => view_main::USER_WORK 
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden', 
            'name' => Main::ASSIGN_PAGE, 
            'value' => Main::TEMPLATE_CORRECT 
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $text = get_string('correct_default_task', 'coursework');
        $btn.= \html_writer::tag('button', $text);

        $attr = array(
            'method' => 'post',
            'style' => 'display: inline-block'
        );
        return \html_writer::tag('form', $btn, $attr);
    }

    private function get_create_task_button() : string 
    {
        $btn = $this->get_neccessary_form_params();

        $attr = array(
            'type' => 'hidden', 
            'name' => view_main::GUI_EVENT, 
            'value' => view_main::USER_WORK 
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden', 
            'name' => Main::ASSIGN_PAGE, 
            'value' => Main::NEW_TASK 
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $text = get_string('create_new_task', 'coursework');
        $btn.= \html_writer::tag('button', $text);

        $attr = array(
            'method' => 'post',
            'style' => 'display: inline-block'
        );
        return \html_writer::tag('form', $btn, $attr);
    }

}
