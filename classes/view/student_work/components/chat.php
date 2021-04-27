<?php

namespace Coursework\View\StudentWork\Components;

use Coursework\View\StudentWork\Locallib as locallib;
use Coursework\Lib\Getters\StudentsGetter as sg;

class Chat extends Base 
{
    private $work;
    private $messages;

    function __construct(\stdClass $course, \stdClass $cm, int $studentId)
    {
        parent::__construct($course, $cm, $studentId);

        $this->work = sg::get_students_work($cm->instance, $studentId);
        $this->messages = $this->get_messages();
    }

    protected function get_hiding_class_name() : string
    {
        return 'work_chat_content';
    }

    protected function get_header_text() : string
    {
        return get_string('chat', 'coursework');
    }

    private function get_messages()
    {
        global $DB;
        $sql = 'SELECT *
                FROM {coursework_chat} 
                WHERE coursework = ?
                AND ((userfrom = ? AND userto =?)
                OR (userfrom = ? AND userto =?))
                ORDER BY sendtime';
        $params = array($this->cm->instance, 
                        $this->work->student, $this->work->teacher,
                        $this->work->teacher, $this->work->student);
        return $DB->get_records_sql($sql, $params);
    }

    protected function get_content() : string
    {
        $c = $this->get_messages_box();

        if(locallib::is_user_student_or_teacher($this->work))
        {
            $c.= $this->get_send_message_button();
        }
        
        return $c;
    }

    private function get_messages_box() : string 
    {
        $text = '';

        foreach($this->messages as $message)
        {
            $text.= $this->get_chat_message($message);
        }

        $text.= $this->get_last_message_anchor();

        $attr = array('class' => 'workChat');
        return \html_writer::tag('div', $text, $attr);
    }

    private function get_chat_message(\stdClass $message) : string 
    {
        if($this->is_student_sent_message($message))
        {
            $attr = array('class' => 'chatMessage studentMessage');
        }
        else 
        {
            $attr = array('class' => 'chatMessage teacherMessage');
        }

        $text = $this->get_message_text($message);
        $text.= $this->get_message_date($message);

        return \html_writer::tag('div', $text, $attr);
    }

    private function is_student_sent_message(\stdClass $message) : bool 
    {
        if($message->userfrom == $this->work->student)
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    private function get_message_text(\stdClass $message) : string 
    {
        $attr = array('class' => 'message');
        $text = $message->message;
        return \html_writer::tag('p', $text, $attr);
    }

    private function get_message_date(\stdClass $message) : string
    {
        $attr = array('class' => 'date');
        $text = date('H:i d-m-Y', $message->sendtime);
        return \html_writer::tag('p', $text, $attr);
    }

    private function get_last_message_anchor() : string 
    {
        $attr = array('id' => 'last_chat_message');
        return \html_writer::tag('p', '', $attr);
    }

    private function get_send_message_button() : string 
    {
        $text = $this->get_send_message_form_start();
        $text.= $this->get_send_message_neccessary_params();

        if(locallib::is_user_student($this->work))
        {
            $text.= $this->get_send_message_student_params();
        }

        if(locallib::is_user_teacher($this->work))
        {
            $text.= $this->get_send_message_teacher_params();
        }

        $text.= $this->get_send_message_input();
        $text.= $this->get_send_message_button_();
        $text.= $this->get_send_message_form_end();

        $attr = array('class' => 'sendMessageBox');
        return \html_writer::tag('div', $text, $attr);
    }

    private function get_send_message_form_start() : string 
    {
        $attr = array('method' => 'post');
        return \html_writer::start_tag('form', $attr);
    }

    private function get_send_message_neccessary_params() : string 
    {
        $attr = array(
            'type' => 'hidden',
            'name' => \ID,
            'value' => $this->cm->id
        );
        $params = \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => \DB_EVENT,
            'value' => \ViewDatabaseHandler::CHAT_MESSAGE
        );
        $params.= \html_writer::empty_tag('input', $attr);

        return $params;
    }

    private function get_send_message_student_params() : string 
    {
        $attr = array(
            'type' => 'hidden',
            'name' => \USERFROM,
            'value' => $this->work->student
        );
        $params = \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => \USERTO,
            'value' => $this->work->teacher
        );
        $params.= \html_writer::empty_tag('input', $attr);

        return $params;
    }

    private function get_send_message_teacher_params() : string 
    {
        $attr = array(
            'type' => 'hidden',
            'name' => \USERFROM,
            'value' => $this->work->teacher
        );
        $params = \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => \USERTO,
            'value' => $this->work->student
        );
        $params.= \html_writer::empty_tag('input', $attr);

        return $params;
    }

    private function get_send_message_input() : string 
    {
        $attr = array(
            'class' => 'sendMessageInput',
            'type' => 'text',
            'name' => \MESSAGE,
            'required' => 'required',
            'minlength' => 1,
            'autocomplete' => 'off'
        );
        return \html_writer::empty_tag('input', $attr);
    }

    private function get_send_message_button_() : string 
    {
        $attr = array('class' => 'sendMessageButton');
        $text = get_string('send', 'coursework');
        return \html_writer::tag('button', $text, $attr);
    }

    private function get_send_message_form_end() : string 
    {
        return \html_writer::end_tag('form');
    }



}
