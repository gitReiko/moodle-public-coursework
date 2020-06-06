<?php

use coursework_lib as lib;

class Chat extends ViewModule 
{

    private $work;
    private $messages;
    private $formId = 'send_message';

    function __construct(stdClass $course, stdClass $cm, int $studentId, bool $displayBlock = false)
    {
        parent::__construct($course, $cm, $studentId, $displayBlock);

        $this->work = lib\get_student_work($this->cm, $this->studentId);
        $this->messages = $this->get_messages();

        $this->mark_messages_as_readed();
    }

    protected function get_module_name() : string
    {
        return 'chat';
    }

    protected function get_module_header() : string
    {
        return get_string('chat', 'coursework');
    }

    protected function get_module_body() : string
    {
        $body = $this->get_start_of_chat_body();
        $body.= $this->get_chat_history();

        global $USER;
        if(lib\is_user_student($this->cm, $USER->id))
        {
            $body.= $this->get_send_message_to_teacher_button();
        }
        else if(lib\is_user_teacher($this->cm, $USER->id))
        {
            $body.= $this->get_send_message_to_student_button();
        }

        $body.= $this->get_end_of_chat_body();
        return $body;
    }

    private function mark_messages_as_readed()
    {
        global $USER;
        foreach($this->messages as $message)
        {
            if($message->readed == '0'
                && $message->userto == $USER->id)
            {
                $db = new MarkMessageAsReadedDatabaseHandler($this->course, $this->cm, $message->id);
                $db->handle();
            }
        }
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

    private function get_start_of_chat_body() : string 
    {
        return '<div class="chat">';
    }

    private function get_chat_history() : string 
    {
        $chat = '';
        foreach($this->messages as $message)
        {
            if($message->userfrom == $this->work->student)
            {
                $chat.= '<div class="studentMessage chatMessage">';
                $chat.= cw_get_user_photo($this->work->student);
            }
            else 
            {
                $chat.= '<div class="teacherMessage chatMessage">';
                $chat.= cw_get_user_photo($this->work->teacher);
            }

            $chat.= '<p class="message">'.$message->message.'</p>';
            $chat.= '<p class="date">'.date('H:i d-m-Y', $message->sendtime).'</p>';     

            $chat.= '</div>';
        }

        return $chat;
    }

    private function get_send_message_to_teacher_button() : string 
    {
        $str = $this->get_send_message_button();
        $str.= $this->get_student_user_inputs();
        return $str;
    }

    private function get_send_message_to_student_button() : string 
    {
        $str = $this->get_send_message_button();
        $str.= $this->get_teacher_user_inputs();
        return $str;
    }

    private function get_send_message_button()
    {
        $btn = '<p>';
        $btn.= '<form class="send_message" id="'.$this->formId.'" method="post">';
        $btn.= '<input type="hidden" name="'.ID.'" value="'.$this->cm->id.'"/>';
        $btn.= '<input type="text" name="'.MESSAGE.'" required minlength="1" />';
        $btn.= '<input type="hidden" name="'.DB_EVENT.'" value="'.ViewDatabaseHandler::CHAT_MESSAGE.'">';
        $btn.= '<button>'.get_string('send', 'coursework').'</button>';
        $btn.= '</form>';
        $btn.= '</p>';
        return $btn;
    }

    private function get_student_user_inputs() : string 
    {
        $btn = '<input type="hidden" name="'.USERFROM.'" value="'.$this->work->student.'" form="'.$this->formId.'"/>';
        $btn.= '<input type="hidden" name="'.USERTO.'" value="'.$this->work->teacher.'" form="'.$this->formId.'"/>';
        return $btn;
    }

    private function get_teacher_user_inputs() : string 
    {
        $btn = '<input type="hidden" name="'.USERFROM.'" value="'.$this->work->teacher.'" form="'.$this->formId.'"/>';
        $btn.= '<input type="hidden" name="'.USERTO.'" value="'.$this->work->student.'" form="'.$this->formId.'"/>';
        return $btn;
    }

    private function get_end_of_chat_body() : string 
    {
        $body = '<a name="last_message"></a>';
        $body.= '</div>';
        return $body;
    }

}

