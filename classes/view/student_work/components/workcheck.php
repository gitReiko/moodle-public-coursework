<?php

namespace Coursework\View\StudentsWork\Components;

use Coursework\View\StudentsWork\Locallib as locallib;
use Coursework\Lib\Getters\StudentsGetter as sg;

class WorkCheck extends Base 
{
    private $work;

    function __construct(\stdClass $course, \stdClass $cm, int $studentId)
    {
        parent::__construct($course, $cm, $studentId);

        $this->work = sg::get_students_work($this->cm->instance, $this->studentId);
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

        if(locallib::is_user_student($this->work)) 
        {
            if(locallib::is_state_not_ready_or_need_to_fix($this->work->status))
            {
                $con.= $this->get_sent_to_check_button();
            }
        }
        else if(locallib::is_user_teacher($this->work))
        {
            if(locallib::is_state_sent_for_check($this->work->status))
            {

            }
        }

        return $con;
    }

    private function get_sent_to_check_button() : string 
    {
        $attr = array(
            'type' => 'hidden',
            'name' => ID,
            'value' => $this->cm->id
        );
        $btn = \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => STUDENT,
            'value' => $this->studentId
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => DB_EVENT,
            'value' => \ViewDatabaseHandler::SEND_WORK_FOR_CHECK
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $text = get_string('send_for_check_work', 'coursework');
        $btn.= \html_writer::tag('button', $text);


        $attr = array('method' => 'post', 'style' => 'display:inline-block;');
        return \html_writer::tag('form', $btn, $attr);
    }




}
