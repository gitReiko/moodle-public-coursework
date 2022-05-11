<?php

namespace Coursework\View\DatabaseHandlers;

use Coursework\Lib\Database\AddNewStudentSectionStatus;
use Coursework\View\DatabaseHandlers\Main as MainDB;
use Coursework\Lib\Getters\UserGetter as ug;
use Coursework\Lib\Notification;
use Coursework\Lib\Enums;

class CheckTaskSection
{
    private $course;
    private $cm;

    private $studentId;
    private $teacherId;
    private $sectionId;
    private $status;

    function __construct(\stdClass $course, \stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->studentId = $this->get_student_id();
        $this->sectionId = $this->get_section_id();
        $this->status = $this->get_status();
        $this->teacherId = $this->get_teacher_id();
    }

    public function handle()
    {
        $addNewStatus = new AddNewStudentSectionStatus(
            $this->cm->instance, 
            $this->studentId, 
            $this->sectionId,
            $this->status
        );

        if($addNewStatus->execute())
        {
            $this->send_notification();

            if($this->is_section_status_returned_for_rework())
            {
                $this->log_event_teacher_sent_section_for_rework();
            }
            else 
            {
                $this->log_event_teacher_accepted_section();
            }
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

    private function get_status() : string  
    {
        $status= optional_param(MainDB::STATUS, null, PARAM_TEXT);
        if(empty($status)) throw new \Exception('Missing status.');
        return $status;
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
        $cm = $this->cm;
        $course = $this->course;
        $userFrom = ug::get_user($this->teacherId); 
        $userTo = ug::get_user($this->studentId); 
        $messageName = 'sectioncheck';
        $messageText = get_string('section_send_for_cheack_header','coursework');

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

    private function is_section_status_returned_for_rework() : bool 
    {
        if($this->status == Enums::RETURNED_FOR_REWORK)
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    private function log_event_teacher_sent_section_for_rework() : void 
    {
        $params = array
        (
            'relateduserid' => $this->studentId,
            'context' => \context_module::instance($this->cm->id)
        );
        
        $event = \mod_coursework\event\teacher_sent_section_for_rework::create($params);
        $event->trigger();
    }

    private function log_event_teacher_accepted_section() : void 
    {
        $params = array
        (
            'relateduserid' => $this->studentId,
            'context' => \context_module::instance($this->cm->id)
        );
        
        $event = \mod_coursework\event\teacher_accepted_section::create($params);
        $event->trigger();
    }

}
