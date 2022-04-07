<?php

namespace Coursework\View\DatabaseHandlers;

use Coursework\View\DatabaseHandlers\Main as MainDB;
use Coursework\Lib\Getters\StudentsGetter as sg;
use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\Notification;
use Coursework\Lib\Enums;

class SendWorkForCheck 
{
    private $course;
    private $cm;

    private $work;
    private $status;

    function __construct(\stdClass $course, \stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->work = $this->get_student_work();
        $this->status = $this->get_status();
    }

    public function handle()
    {
        global $DB;
        if($DB->insert_record('coursework_students_statuses', $this->status)) 
        {
            $this->send_notification();
            $this->log_event_student_sent_work_for_check();
        }
        else 
        {
            throw new \Exception('Student task state "sent_for_check" not added.');
        }
    }

    private function get_student_work() : \stdClass 
    {
        $student = $this->get_student_id_from_request();
        $student = sg::get_student_work($this->cm->instance, $student);
        return $student;
    }

    private function get_student_id_from_request() : int 
    {
        $student = optional_param(MainDB::STUDENT, null, PARAM_INT);
        if(empty($student)) throw new Exception('Missing student id.');
        return $student;
    }

    private function get_status() : \stdClass 
    {
        $state = new \stdClass;
        $state->coursework = $this->work->coursework;
        $state->student = $this->work->student;
        $state->type = Enums::COURSEWORK;
        $state->instance = $this->work->coursework;
        $state->status = Enums::SENT_FOR_CHECK;
        $state->changetime = time();

        return $state;
    }

    private function send_notification() : void 
    {
        global $USER;

        $cm = $this->cm;
        $course = $this->course;
        $userFrom = $USER;
        $userTo = cg::get_user($this->work->teacher); 
        $messageName = 'sendworkforcheck';
        $messageText = get_string('work_send_for_check_header','coursework');

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

    private function log_event_student_sent_work_for_check() : void 
    {
        $params = array
        (
            'context' => \context_module::instance($this->cm->id)
        );
        
        $event = \mod_coursework\event\student_sent_work_for_check::create($params);
        $event->trigger();
    }


}
