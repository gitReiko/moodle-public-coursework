<?php

namespace Coursework\View\DatabaseHandlers;

use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\Notification;

class SectionsCheck 
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
        if($this->update_section_status())
        {
            $work = $this->get_student_coursework();
            $this->send_notification($work);

            $this->log_event_teacher_check_section();
        }
    }

    private function get_section_status() : \stdClass 
    {
        $sectionStatus = new \stdClass;
        $sectionStatus->coursework = $this->get_coursework();
        $sectionStatus->student = $this->get_student();
        $sectionStatus->section = $this->get_section();
        $sectionStatus->status = $this->get_status();
        $sectionStatus->timemodified = time();
        return $sectionStatus;
    }

    private function get_coursework() : int 
    {
        if(empty($this->cm->instance)) throw new \Exception('Missing coursework id.');
        return $this->cm->instance;
    }

    private function get_student() : int 
    {
        $student = optional_param(STUDENT, null, PARAM_INT);
        if(empty($student)) throw new \Exception('Missing student id.');
        return $student;
    }

    private function get_section() : int 
    {
        $section= optional_param(SECTION, null, PARAM_INT);
        if(empty($section)) throw new \Exception('Missing section id.');
        return $section;
    }

    private function get_status() : string  
    {
        $status= optional_param(STATUS, null, PARAM_TEXT);
        if(empty($status)) throw new \Exception('Missing status.');
        return $status;
    }

    private function get_section_status_id() : int  
    {
        global $DB;
        $where = array('coursework'=>$this->cm->instance, 
                        'student' => $this->sectionStatus->student,
                        'section' => $this->sectionStatus->section);
        return $DB->get_field('coursework_sections_status', 'id', $where);
    }

    private function update_section_status()
    {
        global $DB;
        $this->sectionStatus->id = $this->get_section_status_id();
        return $DB->update_record('coursework_sections_status', $this->sectionStatus);
    }

    private function get_student_coursework() : \stdClass
    {
        global $DB;
        $where = array('coursework' => $this->cm->instance, 'student' => $this->sectionStatus->student);
        return $DB->get_record('coursework_students', $where);
    }

    private function send_notification(\stdClass $work) : void 
    {
        $cm = $this->cm;
        $course = $this->course;
        $userFrom = cg::get_user($work->teacher); 
        $userTo = cg::get_user($work->student); 
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

    private function log_event_teacher_check_section() : void 
    {
        $params = array
        (
            'relateduserid' => $this->get_student(),
            'context' => \context_module::instance($this->cm->id)
        );
        
        $event = \mod_coursework\event\teacher_check_section::create($params);
        $event->trigger();
    }



}
