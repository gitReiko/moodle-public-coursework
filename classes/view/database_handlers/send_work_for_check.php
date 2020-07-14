<?php

use coursework_lib as lib;

class SendWorkForCheckDatabaseHandler 
{
    private $course;
    private $cm;

    private $work;

    function __construct(stdClass $course, stdClass $cm)
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


    private function get_work() : stdClass 
    {
        $student = $this->get_student();
        $work = lib\get_student_work($this->cm, $student);
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

    private function get_student_coursework() : stdClass
    {
        global $DB, $USER;
        $where = array('coursework' => $this->cm->instance, 'student' => $USER->id);
        return $DB->get_record('coursework_students', $where);
    }

    private function send_notification(stdClass $work) : void 
    {
        global $USER;

        $cm = $this->cm;
        $course = $this->course;
        $messageName = 'sendworkforcheck';
        $userFrom = $USER;
        $userTo = lib\get_user($work->teacher); 
        $headerMessage = get_string('work_send_for_cheack_header','coursework');
        $giveTask = true;
        $fullMessageHtml = $this->get_select_theme_html_message($giveTask);

        lib\send_notification($cm, $course, $messageName, $userFrom, $userTo, $headerMessage, $fullMessageHtml);
    }

    private function get_select_theme_html_message($giveTask = false) : string
    {
        $message = '<p>'.get_string('work_send_for_cheack_header','coursework', $params).'</p>';
        $notification = get_string('answer_not_require', 'coursework');

        return cw_get_html_message($this->cm, $this->course->id, $message, $notification);
    }


}
