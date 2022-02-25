<?php

namespace Coursework\View\StudentWork\Components\Task;

use Coursework\View\DatabaseHandlers\Main as MainDB;
use Coursework\View\StudentWork\Locallib as locallib;
use Coursework\View\DatabaseHandlers\Main as db;
use Coursework\Lib\Enums as enum;

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
        if(locallib::is_state_started_or_returned_for_rework($this->section->status))
        {
            return $this->get_sent_for_check_button();
        }
        else 
        {
            return '';
        }
    }

    private function get_sent_for_check_button() : string 
    {
        $btn = $this->get_common_form_inputs();

        $attr = array(
            'type' => 'hidden',
            'name' => MainDB::DB_EVENT,
            'value' => db::SEND_SECTION_FOR_CHECK
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
            'name' => MainDB::ID,
            'value' => $this->cm->id
        );
        $inputs = \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => MainDB::SECTION,
            'value' => $this->section->id
        );
        $inputs.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => MainDB::STUDENT,
            'value' => $this->work->student
        );
        $inputs.= \html_writer::empty_tag('input', $attr);

        return $inputs;
    }

    private function get_teacher_buttons() : string 
    {
        $btn = '';

        if(locallib::is_state_sent_for_check($this->section->status))
        {
            $btn.= $this->get_accept_section_button();
            $btn.= $this->get_send_for_rework_button();
        }
        else if($this->is_section_ready())
        {
            if($this->is_coursework_started())
            {
                $btn.= $this->get_send_for_rework_button();
            }
        }

        return $btn;
    }

    private function is_section_ready() : bool 
    {
        if($this->section->status == MainDB::READY)
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    private function is_coursework_started() : bool 
    {
        if($this->work->status == MainDB::READY)
        {
            return false;
        }
        else 
        {
            return true;
        }
    }

    private function get_accept_section_button() : string 
    {
        $btn = $this->get_common_form_inputs();

        $attr = array(
            'type' => 'hidden',
            'name' => MainDB::STATUS,
            'value' => enum::READY
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => MainDB::DB_EVENT,
            'value' => db::SECTION_CHECK
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
            'name' => MainDB::STATUS,
            'value' => enum::RETURNED_FOR_REWORK
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => MainDB::DB_EVENT,
            'value' => db::SECTION_CHECK
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $text = get_string('send_for_rework', 'coursework');
        $btn.= \html_writer::tag('button', $text);

        $attr = array('method' => 'post', 'style' => 'display:inline-block;');
        return \html_writer::tag('form', $btn, $attr);
    }

}
