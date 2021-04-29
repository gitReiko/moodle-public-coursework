<?php

namespace Coursework\View\DatabaseHandlers;

use coursework_lib as lib;

class UseTaskTemplateDatabaseHandler 
{
      
    private $course;
    private $cm;

    function __construct(\stdClass $course, \stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;
    }

    public function handle()
    {
        $work = $this->get_student_work();
        $this->update_student_work($work);
    }

    private function get_student_work() : \stdClass 
    {
        $studentId = $this->get_student_id();
        $work = lib\get_student_work($this->cm, $studentId);
        $taskTemplate = lib\get_using_task($this->cm);

        $studentWork = new \stdClass;
        $studentWork->id = $work->id;
        $studentWork->task = $taskTemplate->id;

        return $studentWork;
    }

    private function get_student_id() : int 
    {
        $studentId = optional_param(STUDENT.ID, null, PARAM_INT);
        if(empty($studentId)) throw new Exception('Missing student id');
        return $studentId;
    }

    private function update_student_work(\stdClass $work)
    {
        global $DB;
        if($DB->update_record('coursework_students', $work))
        {
            $this->send_notification_to_student($work);
        }
    }

    private function send_notification_to_student(\stdClass $row) : void 
    {
        global $USER;

        $cm = $this->cm;
        $course = $this->course;
        $messageName = 'taskassignment';
        $userFrom = $USER;
        $userTo = lib\get_user($this->get_student_id()); 
        $headerMessage = get_string('task_assignment_header','coursework');
        $fullMessageHtml = $this->get_student_html_message();

        lib\send_notification($cm, $course, $messageName, $userFrom, $userTo, $headerMessage, $fullMessageHtml);

    }

    private function get_student_html_message() : string
    {
        $params = cw_prepare_data_for_message();
        $message = get_string('task_assignment_header','coursework', $params);
        $notification = get_string('answer_not_require', 'coursework');

        return cw_get_html_message($this->cm, $this->course->id, $message, $notification);
    }



}
