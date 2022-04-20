<?php

namespace Coursework\View\DatabaseHandlers;

use Coursework\Lib\Database\AddNewStatusToAllSections;
use Coursework\Lib\Database\AddNewStudentWorkStatus;
use Coursework\View\DatabaseHandlers\Main as MainDB;
use Coursework\Lib\Getters\StudentsGetter as sg;
use Coursework\Lib\Getters\StudentTaskGetter;
use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\Notification;
use Coursework\Lib\Enums;

class SendWorkForCheck 
{
    private $course;
    private $cm;

    private $work;
    private $uncheckedSections;

    function __construct(\stdClass $course, \stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->work = $this->get_student_work();
        $this->uncheckedSections = $this->get_unchecked_sections();
    }

    public function handle()
    {
        $addNewStatus = new AddNewStudentWorkStatus(
            $this->work->coursework, 
            $this->work->student, 
            Enums::SENT_FOR_CHECK 
        );
        
        if($addNewStatus->execute())
        {
            $this->set_sent_for_check_status_to_unchecked_sections();
            $this->send_notification();
            $this->log_event_student_sent_work_for_check();
        }
    }

    private function get_student_work() : \stdClass 
    {
        $studentId = $this->get_student_id_from_request();
        return sg::get_student_work($this->cm->instance, $studentId);
    }

    private function get_student_id_from_request() : int 
    {
        $student = optional_param(MainDB::STUDENT, null, PARAM_INT);
        if(empty($student)) throw new Exception('Missing student id.');
        return $student;
    }

    private function get_unchecked_sections()
    {
        $sections = $this->get_all_student_task_sections();
        $sections = $this->filter_leave_only_unchecked($sections);
        return $sections;
    }

    private function get_all_student_task_sections()
    {
        $sections = new StudentTaskGetter(
            $this->cm->instance, 
            $this->work->student
        );
        return $sections->get_sections();
    }

    private function filter_leave_only_unchecked($sections)
    {
        $uncheked = array();

        foreach($sections as $section)
        {
            if($section->latestStatus == Enums::READY)
            {
                continue;
            }
            else if($section->latestStatus == Enums::SENT_FOR_CHECK)
            {
                continue;
            }

            $uncheked[] = $section;
        }

        return $uncheked;
    }

    private function set_sent_for_check_status_to_unchecked_sections() : void 
    {
        $addNewStatus = new AddNewStatusToAllSections(
            $this->work,
            $this->uncheckedSections,
            Enums::SENT_FOR_CHECK
        );
        $addNewStatus->execute();
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
