<?php

namespace Coursework\View\StudentWork\Components;

use Coursework\View\DatabaseHandlers\Main as MainDB;
use Coursework\View\StudentWork\Locallib as locallib;
use Coursework\Lib\Getters\StudentsGetter as sg;
use Coursework\Lib\Enums as enum;
use Coursework\View\DatabaseHandlers\Main as db;

class WorkCheck extends Base 
{
    private $student;

    function __construct(\stdClass $course, \stdClass $cm, int $studentId)
    {
        parent::__construct($course, $cm, $studentId);

        $this->student = sg::get_student_with_his_work($this->cm->instance, $this->studentId);
    }

    protected function get_hiding_class_name() : string
    {
        return 'work_workcheck_content';
    }

    protected function get_header_text() : string
    {
        return get_string('work_check', 'coursework');
    }

    protected function get_content() : string
    {
        $con = '';

        if(locallib::is_user_student($this->student)) 
        {
            if(locallib::is_state_started_or_returned_for_rework($this->student->latestStatus))
            {
                $con.= $this->get_sent_for_check_button();
            }
        }
        else if(locallib::is_user_teacher($this->student))
        {
            if(locallib::is_state_sent_for_check($this->student->latestStatus))
            {
                $con.= $this->get_teacher_check_work_block();
            }
            else if(locallib::is_state_ready($this->student->latestStatus))
            {
                $con.= $this->get_teacher_regrade_block();
            }
        }

        return $con;
    }

    private function get_sent_for_check_button() : string 
    {
        $btn = $this->get_common_form_inputs();

        $attr = array(
            'type' => 'hidden',
            'name' => MainDB::DB_EVENT,
            'value' => db::SEND_WORK_FOR_CHECK
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $text = get_string('send_for_check_work', 'coursework');
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
        $btn = \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => MainDB::STUDENT,
            'value' => $this->studentId
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        return $btn;
    }

    private function get_teacher_check_work_block() : string 
    {
        $text = get_string('teacher_work_check_header', 'coursework');
        $block = \html_writer::tag('p', $text);
        $block.= \html_writer::empty_tag('hr');
        $block.= $this->get_send_for_rework_button();
        $block.= \html_writer::empty_tag('hr');
        $block.= $this->get_grade_and_regrade_button();

        return $block;
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
            'value' => db::WORK_CHECK
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $text = get_string('send_for_rework', 'coursework');
        $btn.= \html_writer::tag('button', $text);

        $attr = array('method' => 'post', 'style' => 'display:inline-block;');
        $btn = \html_writer::tag('form', $btn, $attr);

        return \html_writer::tag('p', $btn);
    }

    private function get_teacher_regrade_block() : string 
    {
        $text = get_string('teacher_regrade_header', 'coursework');
        $block = \html_writer::tag('p', $text);
        $block.= \html_writer::empty_tag('hr');
        $block.= $this->get_grade_and_regrade_button();

        return $block;
    }

    private function get_grade_and_regrade_button() : string 
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
            'value' => db::WORK_CHECK
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'number',
            'name' => MainDB::GRADE,
            'size' => 4,
            'min' => 0,
            'max' => 10000,
            'autocomplete' => 'off',
            'required' => 'required'
        );
        $btn.= \html_writer::empty_tag('input', $attr).' ';

        if(locallib::is_state_sent_for_check($this->student->latestStatus))
        {
            $text = get_string('accept_work_and_grade', 'coursework');
        }
        else 
        {
            $text = get_string('regrade', 'coursework');
        }
        
        $btn.= \html_writer::tag('button', $text);

        $attr = array('method' => 'post', 'style' => 'display:inline-block;');
        $btn = \html_writer::tag('form', $btn, $attr);

        return \html_writer::tag('p', $btn);
    }


}
