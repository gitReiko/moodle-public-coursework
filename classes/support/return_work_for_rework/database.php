<?php

namespace Coursework\Support\BackToWorkState;

use Coursework\Lib\Database\AddNewStatusToAllSections;
use Coursework\Lib\Database\AddNewStudentWorkStatus;
use Coursework\Lib\Getters\StudentsGetter as sg;
use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\CommonLib as cl;
use Coursework\Lib\Notification;
use Coursework\Lib\Feedbacker;
use Coursework\Lib\Enums;

class Database 
{
    private $cm;
    private $course;

    private $studentWork;

    function __construct(\stdClass $cm, \stdClass $course) 
    {
        $this->cm = $cm;
        $this->course = $course;

        $this->studentWork = sg::get_student_work(
            $this->cm->instance, 
            $this->get_student_id()
        );
    }

    public function change_state_to_work() : string   
    {
        $feedback = '';

        if($this->is_student_work_exist())
        {
            $feedbackItem = $this->return_to_work_state();
        }
        else 
        {
            $feedbackItem = $this->get_fail_feedback();
        }

        return Feedbacker::add_feedback_to_string($feedback, $feedbackItem);
    }

    private function get_student_id() : int 
    {
        $student = optional_param(Main::STUDENT_ID, null, PARAM_INT);

        if(empty($student)) throw new \Exception('Missing student id.');

        return $student;
    }

    private function is_student_work_exist() : bool 
    {
        if(empty($this->studentWork->id)) return false;
        else return true;
    }

    private function return_to_work_state() : \stdClass 
    {
        if($this->set_status_returned_for_rework_to_student_work())
        {
            if(cl::is_coursework_use_task($this->cm->instance))
            {
                $this->set_status_returned_for_rework_to_task_sections();
            }
            
            $this->send_notification_to_student();
            $this->log_return_student_work_for_rework_event();
            return $this->get_success_feedback();
        }
    }

    private function set_status_returned_for_rework_to_student_work()
    {
        $addNewStatus = new AddNewStudentWorkStatus(
            $this->studentWork->coursework, 
            $this->studentWork->student, 
            Enums::RETURNED_FOR_REWORK 
        );
        return $addNewStatus->execute();
    }

    private function set_status_returned_for_rework_to_task_sections() : void 
    {
        $addNewStatus = new AddNewStatusToAllSections(
            $this->studentWork,
            cg::get_task_sections($this->studentWork->task),
            Enums::RETURNED_FOR_REWORK
        );
        $addNewStatus->execute();
    }

    private function send_notification_to_student() : void 
    {
        global $USER;

        $cm = $this->cm;
        $course = $this->course;
        $userFrom = $USER;
        $userTo = cg::get_user($this->studentWork->student); 
        $messageName = 'return_work_for_rework';
        $messageText = $this->get_message_text();

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

    private function get_message_text() : string 
    {
        $text = get_string('coursework_returned_to_work_state','coursework');
        $text.= get_string('answer_not_require', 'coursework');

        return $text;
    }

    private function log_return_student_work_for_rework_event()
    {
        $params = array
        (
            'relateduserid' => $this->studentWork->student, 
            'context' => \context_module::instance($this->cm->id)
        );

        $event = \mod_coursework\event\return_student_work_for_rework::create($params);
        $event->trigger();
    }

    private function get_success_feedback() : \stdClass  
    {
        $text = get_string('return_student_work_for_rework', 'coursework');
        return Feedbacker::get_success_feedback($text);
    }

    private function get_fail_feedback() : \stdClass  
    {
        $text = get_string('impossible_return_to_work_state', 'coursework');
        return Feedbacker::get_fail_feedback($text);
    }


}

