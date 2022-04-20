<?php

namespace Coursework\View\DatabaseHandlers;

use Coursework\Lib\Database\AddNewStudentSectionStatus;
use Coursework\View\DatabaseHandlers\Main as MainDB;
use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\Notification;
use Coursework\Lib\Enums;

class SendSectionForCheck 
{
    private $course;
    private $cm;

    private $studentId;
    private $sectionId;

    function __construct(\stdClass $course, \stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->studentId = $this->get_student_id();
        $this->sectionId = $this->get_section_id();
        $this->teacherId = $this->get_teacher_id();
    }

    public function handle()
    {
        $addNewStatus = new AddNewStudentSectionStatus(
            $this->cm->instance, 
            $this->studentId, 
            $this->sectionId,
            Enums::SENT_FOR_CHECK 
        );

        if($addNewStatus->execute())
        {
            $this->send_notification();
            $this->log_event_student_sent_section_for_check();
        }
    }

    private function get_student_id() : int 
    {
        $student = optional_param(MainDB::STUDENT, null, PARAM_INT);
        if(empty($student)) throw new \Exception('Missing student id.');
        return $student;
    }

    private function get_section_id() : int 
    {
        $section= optional_param(MainDB::SECTION, null, PARAM_INT);
        if(empty($section)) throw new \Exception('Missing section id.');
        return $section;
    }

    private function get_teacher_id() : int 
    {
        global $DB;
        $where = array(
            'coursework' => $this->cm->instance, 
            'student' => $this->studentId
        );
        return $DB->get_field('coursework_students', 'teacher', $where);
    }

    private function send_notification() : void 
    {
        global $USER;

        $cm = $this->cm;
        $course = $this->course;
        $userFrom = $USER;
        $userTo = cg::get_user($this->teacherId); 
        $messageName = 'sendsectionforcheck';
        $messageText = get_string('section_check','coursework');

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

    private function log_event_student_sent_section_for_check() : void 
    {
        $params = array
        (
            'context' => \context_module::instance($this->cm->id)
        );
        
        $event = \mod_coursework\event\student_sent_section_for_check::create($params);
        $event->trigger();
    }


}
