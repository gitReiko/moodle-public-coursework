<?php

namespace Coursework\View\DatabaseHandlers;

use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\Notification;

class ChatMessage 
{
    private $course;
    private $cm;

    private $message;

    function __construct(\stdClass $course, \stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->message = $this->get_message();

        $this->send_notification();
    }

    public function handle()
    {
        $this->add_message_to_database();
    }

    private function get_message() : \stdClass 
    {
        $message = new \stdClass;
        $message->coursework = $this->get_coursework();
        $message->userfrom = $this->get_user_from();
        $message->userto = $this->get_user_to();
        $message->message = $this->get_message_text();
        $message->sendtime = time();
        $message->readed = 0;
        return $message;
    }

    private function get_coursework() : int 
    {
        if(empty($this->cm->instance)) throw new \Exception('Missing coursework id.');
        return $this->cm->instance;
    }

    private function get_user_from() : int 
    {
        $userFrom = optional_param(USERFROM, null, PARAM_INT);
        if(empty($userFrom)) throw new \Exception('Missing user from id.');
        return $userFrom;
    }

    private function get_user_to() : int 
    {
        $userTo= optional_param(USERTO, null, PARAM_INT);
        if(empty($userTo)) throw new \Exception('Missing user to id.');
        return $userTo;
    }

    private function get_message_text() : string 
    {
        $message= optional_param(MESSAGE, null, PARAM_TEXT);
        if(empty($message)) throw new \Exception('Missing message.');
        return $message;
    }

    private function add_message_to_database()
    {
        global $DB;
        return $DB->insert_record('coursework_chat', $this->message);
    }

    private function send_notification() : void 
    {
        $cm = $this->cm;
        $course = $this->course;
        $userFrom = cg::get_user($this->message->userfrom); 
        $userTo = cg::get_user($this->message->userto); 
        $messageName = 'chatmessage';
        $messageText = get_string('chat_message','coursework');

        $notification = new Notification(
            $cm,
            $course,
            $userFrom,
            $userTo,
            $messageName,
            $messageText
        );

        $notification->send();
    }




}
