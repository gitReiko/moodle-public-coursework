<?php

namespace Coursework\View\DatabaseHandlers;

use Coursework\View\DatabaseHandlers\Main as MainDB;
use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\Notification;
use Coursework\Lib\Enums;

class SendSectionForCheck 
{
    private $course;
    private $cm;

    private $sectionStatus;

    function __construct(\stdClass $course, \stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->sectionStatus = $this->get_section_status();
    }

    public function handle()
    {
        if($this->add_section_status())
        {
            $work = $this->get_student_coursework();
            $this->send_notification($work);
    
            $this->log_event_student_sent_section_for_check();
        }
    }

    private function get_section_status() : \stdClass 
    {
        $sectionStatus = new \stdClass;
        $sectionStatus->coursework = $this->get_coursework();
        $sectionStatus->student = $this->get_student();
        $sectionStatus->type = Enums::SECTION;
        $sectionStatus->instance = $this->get_section();
        $sectionStatus->status = Enums::SENT_FOR_CHECK;
        $sectionStatus->changetime = time();
        return $sectionStatus;
    }

    private function get_coursework() : int 
    {
        if(empty($this->cm->instance)) throw new \Exception('Missing coursework id.');
        return $this->cm->instance;
    }

    private function get_student() : int 
    {
        $student = optional_param(MainDB::STUDENT, null, PARAM_INT);
        if(empty($student)) throw new \Exception('Missing student id.');
        return $student;
    }

    private function get_section() : int 
    {
        $section= optional_param(MainDB::SECTION, null, PARAM_INT);
        if(empty($section)) throw new \Exception('Missing section id.');
        return $section;
    }

    private function add_section_status()
    {
        global $DB;
        return $DB->insert_record('coursework_students_statuses', $this->sectionStatus);
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
