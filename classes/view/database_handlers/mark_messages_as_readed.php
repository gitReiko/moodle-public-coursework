<?php

use coursework_lib as lib;

class MarkMessageAsReadedDatabaseHandler 
{
    private $course;
    private $cm;

    private $message;

    function __construct(stdClass $course, stdClass $cm, int $messageId)
    {
        $this->course = $course;
        $this->cm = $cm;

        if(empty($messageId)) throw new Exception('Missing message id');


        $this->message = $this->get_message($messageId);
    }

    public function handle()
    {
        $this->mark_message_as_readed();
    }

    private function get_message(int $messageId) : stdClass 
    {
        $message = new stdClass;
        $message->id = $messageId;
        $message->readed = 1;
        return $message;
    }

    private function mark_message_as_readed()
    {
        global $DB;
        return $DB->update_record('coursework_chat', $this->message);
    }

}
