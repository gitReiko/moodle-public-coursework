<?php

namespace Coursework\View\StudentWork\Components;

use Coursework\View\StudentWork\Locallib as locallib;
use Coursework\Lib\Getters\StudentsGetter as sg;
use Coursework\Lib\Getters\CommonGetter as cg;

class Chat extends Base 
{
    private $formId;
    private $work;
    private $messages;

    function __construct(\stdClass $course, \stdClass $cm, int $studentId)
    {
        parent::__construct($course, $cm, $studentId);

        $this->formId = 'messageFormId';
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
        $с = $this->get_messages_box();
        if(locallib::is_user_student_or_teacher($this->work))
        {
            $с.= $this->get_send_message_button();
        }

        $attr = array('class' => 'chatWindow');
        $c = \html_writer::tag('div', $с, $attr);
        
        return $c;
    }

    private function get_messages_box() : string 
    {
        $text = '';

        foreach($this->messages as $message)
        {
            if($this->is_student_sent_message($message))
            {
                $text.= $this->get_student_message($message);
            }
            else 
            {
                $text.= $this->get_teacher_message($message);
            }
        }

        $text.= $this->get_last_message_anchor();

        $attr = array('class' => 'newChat');
        return \html_writer::tag('div', $text, $attr);
    }

    private function get_student_message(\stdClass $message) : string 
    {
        $inner = cg::get_chat_user_photo($this->work->student);
        $td = \html_writer::tag('td', $inner);

        $inner = $this->get_message_text($message);
        $inner.= $this->get_message_date($message);
        $td.= \html_writer::tag('td', $inner);

        $tr = \html_writer::tag('tr', $td);

        $attr = array('class' => 'studentMessage');
        $table = \html_writer::tag('table', $tr, $attr);

        return $table;
    }

    private function get_teacher_message(\stdClass $message) : string 
    {
        $inner = $this->get_message_text($message);
        $inner.= $this->get_message_date($message);
        $td = \html_writer::tag('td', $inner);

        $inner = cg::get_chat_user_photo($this->work->teacher);
        $td.= \html_writer::tag('td', $inner);

        $tr = \html_writer::tag('tr', $td);

        $attr = array('class' => 'teacherMessage');
        $table = \html_writer::tag('table', $tr, $attr);

        return $table;
    }

    private function get_send_message_button() : string 
    {
        $attr = array('class' => 'inputMessage');
        $inner = $this->get_message_input();
        $td = \html_writer::tag('td', $inner, $attr);

        $attr = array('class' => 'sendButton');
        $inner = $this->get_message_button();
        $td.= \html_writer::tag('td', $inner, $attr);

        $tr = \html_writer::tag('tr', $td);

        $attr = array('class' => 'sendMessage');
        $table = \html_writer::tag('table', $td, $attr);

        if(locallib::is_user_student_or_teacher($this->work))
        {
            $table.= $this->get_send_message_form();
        }

        return $table;
    }

    private function get_message_input() : string 
    {
        $attr = array(
            'id' => 'chatMessageInput',
            'class' => 'newInputMessage',
            'form' => $this->formId,
            'name' => \MESSAGE,
            'value' => get_string('write_your_message_here', 'coursework'),
            'title' => get_string('write_your_message_here', 'coursework'),
            'onclick' => 'Chat.remove_title_text();',
            'autocomlete' => 'off'
        );
        return \html_writer::empty_tag('input', $attr);
    }

    private function get_message_button() : string 
    {
        $attr = array(
            'class' => 'newSendButton',
            'onclick' => 'Chat.send_chat_message()'
        );
        $text = get_string('send', 'coursework');
        return \html_writer::tag('p', $text, $attr);
    }

    private function get_send_message_form() : string 
    {
        $form = $this->get_neccessary_form_params();

        if(locallib::is_user_student($this->work))
        {
            $form.= $this->get_student_form_params(); 
        }
        else if(locallib::is_user_teacher($this->work))
        {
            $form.= $this->get_teacher_form_params();
        }

        $attr = array(
            'id' => $this->formId,
            'method' => 'post'
        );
        $form = \html_writer::tag('form', $form, $attr);

        return $form;
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

    private function get_neccessary_form_params() : string 
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

    private function get_student_form_params() : string 
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

    private function get_teacher_form_params() : string 
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



}
