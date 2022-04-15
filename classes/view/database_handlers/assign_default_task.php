<?php

namespace Coursework\View\DatabaseHandlers;

use Coursework\View\DatabaseHandlers\Lib\SetStatusStartedToTaskSection;
use Coursework\View\DatabaseHandlers\Main as MainDB;
use Coursework\Lib\Getters\StudentsGetter as sg;
use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\Notification;
use Coursework\Lib\Enums;

class AssignDefaultTask 
{
      
    private $course;
    private $cm;

    private $studentWork;
    private $taskSections;

    function __construct(\stdClass $course, \stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->studentWork = $this->get_student_work();
        $this->taskSections = cg::get_task_sections($this->studentWork->task);
    }

    public function handle()
    {
        global $DB;

        if($DB->update_record('coursework_students', $this->studentWork))
        {
            $this->set_status_started_to_task_section();

            $this->send_notification_to_student($this->studentWork);
            $this->log_event();
        }
    }

    private function get_student_work() : \stdClass 
    {
        $studentId = $this->get_student_id();
        $work = sg::get_student_work($this->cm->instance, $studentId);
        $taskTemplate = cg::get_default_coursework_task($this->cm);

        $studentWork = new \stdClass;
        $studentWork->id = $work->id;
        $studentWork->coursework = $work->coursework;
        $studentWork->student = $work->student;
        $studentWork->task = $taskTemplate->id;

        return $studentWork;
    }

    private function get_student_id() : int 
    {
        $studentId = optional_param(MainDB::STUDENT_ID, null, PARAM_INT);
        if(empty($studentId)) throw new Exception('Missing student id');
        return $studentId;
    }

    private function set_status_started_to_task_section() : void 
    {
        $setStatusStartedToTaskSection = new SetStatusStartedToTaskSection(
            $this->studentWork,
            $this->taskSections
        );
        $setStatusStartedToTaskSection->execute();
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

    private function log_event() : void 
    {
        $params = array
        (
            'relateduserid' => $this->get_student_id(), 
            'context' => \context_module::instance($this->cm->id)
        );
        
        $event = \mod_coursework\event\default_task_assigned_to_student::create($params);
        $event->trigger();
    }

}
