<?php

namespace Coursework\View\StudentsWork\Components\Task;

use Coursework\Lib\Enums as enum;
use Coursework\View\StudentsWork\Locallib as locallib;

class InteractionCell 
{
    private $cm;
    private $work;
    private $section;

    function __construct($cm, $work, $section)
    {
        $this->cm = $cm;
        $this->work = $work;
        $this->section = $section;
    }

    public function get() : string 
    {
        $cell = '';

        if(locallib::is_user_student($this->work)) 
        {
            $cell.= $this->get_student_button();
        }
        else if(locallib::is_user_teacher($this->work))
        {
            $cell.= $this->get_teacher_buttons();
        }
        
        $attr = array('class' => 'center');
        return \html_writer::tag('td', $cell, $attr);
    }

    private function get_student_button() : string 
    {
        if($this->is_section_not_ready())
        {
            return $this->get_sent_to_check_button();
        }
        else 
        {
            return '';
        }
    }

    private function is_section_not_ready() : bool 
    {
        if($this->section->status == enum::NOT_READY)
        {
            return true;
        }
        else if($this->section->status == enum::NEED_TO_FIX)
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    private function get_sent_to_check_button() : string 
    {
        $btn = $this->get_common_form_inputs();

        $attr = array(
            'type' => 'hidden',
            'name' => DB_EVENT,
            'value' => \ViewDatabaseHandler::SEND_SECTION_FOR_CHECK
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $text = get_string('send_for_check', 'coursework');
        $btn.= \html_writer::tag('button', $text);


        $attr = array('method' => 'post', 'style' => 'display:inline-block;');
        return \html_writer::tag('form', $btn, $attr);
    }

    private function get_common_form_inputs() : string 
    {
        $attr = array(
            'type' => 'hidden',
            'name' => ID,
            'value' => $this->cm->id
        );
        $inputs = \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => SECTION,
            'value' => $this->section->id
        );
        $inputs.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => STUDENT,
            'value' => $this->work->student
        );
        $inputs.= \html_writer::empty_tag('input', $attr);

        return $inputs;
    }

    private function get_teacher_buttons() : string 
    {
        $btn = '';

        if($this->is_section_sent_for_check())
        {
            $btn.= $this->get_accept_section_button();
            $btn.= $this->get_send_for_rework_button();
        }

        return $btn;
    }

    private function is_section_sent_for_check() : bool 
    {
        if($this->section->status == enum::SENT_TO_CHECK)
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    private function get_accept_section_button() : string 
    {
        $btn = $this->get_common_form_inputs();

        $attr = array(
            'type' => 'hidden',
            'name' => STATUS,
            'value' => enum::READY
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => DB_EVENT,
            'value' => \ViewDatabaseHandler::SECTION_CHECK
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $text = get_string('accept_sections', 'coursework');
        $btn.= \html_writer::tag('button', $text);

        $attr = array('method' => 'post', 'style' => 'display:inline-block;');
        return \html_writer::tag('form', $btn, $attr);
    }

    private function get_send_for_rework_button() : string 
    {
        $btn = $this->get_common_form_inputs();

        $attr = array(
            'type' => 'hidden',
            'name' => STATUS,
            'value' => enum::NEED_TO_FIX
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => DB_EVENT,
            'value' => \ViewDatabaseHandler::SECTION_CHECK
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $text = get_string('send_for_rework', 'coursework');
        $btn.= \html_writer::tag('button', $text);

        $attr = array('method' => 'post', 'style' => 'display:inline-block;');
        return \html_writer::tag('form', $btn, $attr);
    }

}
