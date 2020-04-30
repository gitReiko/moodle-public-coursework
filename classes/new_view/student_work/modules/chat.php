<?php

use coursework_lib as lib;

class Chat extends ViewModule 
{

    private $work;
    private $messages;

    function __construct(stdClass $course, stdClass $cm, int $studentId, bool $displayBlock = false)
    {
        parent::__construct($course, $cm, $studentId, $displayBlock);

        $this->work = lib\get_student_work($this->cm, $this->studentId);
        $this->messages = $this->get_messages();

        print_r($this->messages);
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
        $body = $this->get_chat_body();


        return $body;
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

    private function get_chat_body() : string 
    {
        $chat = '<div class="chat">';
        foreach($this->messages as $message)
        {
            if($message->userfrom == $this->work->student)
            {
                $chat.= '<div class="studentMessage chatMessage">';
                $chat.= '<div class="photo">'.cw_get_user_photo($this->work->student).'</div>';
            }
            else 
            {
                $chat.= '<div class="teacherMessage chatMessage">';
                $chat.= '<div class="photo">'.cw_get_user_photo($this->work->teacher).'</div>';
            }

            $chat.= '<div class="message">'.$message->message.'</div>';
            $chat.= '<div class="date">'.date('H:i d-m-Y', $message->sendtime).'</div>';

            $chat.= '</div>';
        }


        $chat.= '</div>';
        return $chat;
    }

}

