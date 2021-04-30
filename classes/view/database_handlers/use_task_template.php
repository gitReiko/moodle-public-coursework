<?php

namespace Coursework\View\DatabaseHandlers;

use Coursework\Lib\Getters\StudentsGetter as sg;
use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\Notification;

class UseTaskTemplate 
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
        $work = sg::get_students_work($this->cm->instance, $studentId);
        $taskTemplate = cg::get_default_coursework_task($this->cm);

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
        $userFrom = $USER;
        $userTo = cg::get_user($this->get_student_id()); 
        $messageName = 'taskassignment';
        $messageText = get_string('task_assignment_header','coursework');

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
