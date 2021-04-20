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
            $cell.= $this->get_student_buttons();
        }
        else 
        {

        }
        
        return \html_writer::tag('td', $cell);
    }

    private function get_student_buttons() : string 
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
        $attr = array('method' => 'post');
        $btn = \html_writer::start_tag('form', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => ID,
            'value' => $this->cm->id
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => SECTION,
            'value' => $this->section->id
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => STUDENT,
            'value' => $this->work->student
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => DB_EVENT,
            'value' => \ViewDatabaseHandler::SEND_SECTION_FOR_CHECK
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $text = get_string('send_for_check', 'coursework');
        $btn.= \html_writer::tag('button', $text);

        $btn.= \html_writer::end_tag('form');

        return $btn;
    }


}
