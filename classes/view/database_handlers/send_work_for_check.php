<?php

namespace Coursework\View\DatabaseHandlers;

use Coursework\Lib\Getters\StudentsGetter as sg;
use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\Notification;

class SendWorkForCheck 
{
    private $course;
    private $cm;

    private $work;

    function __construct(\stdClass $course, \stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->work = $this->get_work();

    }

    public function handle()
    {
        $this->update_work_status();

        $work = $this->get_student_coursework();
        $this->send_notification($work);
    }


    private function get_work() : \stdClass 
    {
        $student = $this->get_student();
        $work = sg::get_students_work($this->cm->instance, $student);
        $work->status = SENT_TO_CHECK;
        $work->workstatuschangedate = time();
        return $work;
    }

    private function get_student() : int 
    {
        $student = optional_param(STUDENT, null, PARAM_INT);
        if(empty($student)) throw new Exception('Missing student id.');
        return $student;
    }


    private function update_work_status()
    {
        global $DB;
        return $DB->update_record('coursework_students', $this->work);
    }

    private function get_student_coursework() : \stdClass
    {
        global $DB, $USER;
        $where = array('coursework' => $this->cm->instance, 'student' => $USER->id);
        return $DB->get_record('coursework_students', $where);
    }

    private function send_notification(\stdClass $work) : void 
    {
        global $USER;

        $cm = $this->cm;
        $course = $this->course;
        $userFrom = $USER;
        $userTo = cg::get_user($work->teacher); 
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


}
