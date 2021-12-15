<?php

namespace Coursework\Support\BackToWorkState;

use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\Enums as enum;
use Coursework\Lib\Notification;

class Database 
{
    private $cm;
    private $course;

    function __construct(\stdClass $cm, \stdClass $course) 
    {
        $this->cm = $cm;
        $this->course = $course;
    }

    public function change_state_to_work() : void  
    {
        $studentWork = new \stdClass;
        $studentWork->coursework = $this->get_coursework();
        $studentWork->student = $this->get_student();
        $studentWork->id = $this->get_id($studentWork);
        $studentWork->status = enum::NOT_READY;
        $studentWork->workstatuschangedate = time();

        if($this->is_student_work_exist($studentWork))
        {
            $this->return_to_work_state($studentWork);
        }
        else 
        {
            $this->display_missing_work_message();
        }
    }

    private function get_coursework() : int
    {
        $coursework = optional_param(Main::COURSEWORK_ID, null, PARAM_INT);

        if(empty($coursework)) throw new \Exception('Missing coursework id.');

        return $coursework;
    }

    private function get_student() : int 
    {
        $student = optional_param(Main::STUDENT_ID, null, PARAM_INT);

        if(empty($student)) throw new \Exception('Missing student id.');

        return $student;
    }

    private function get_id(\stdClass $studentWork) : int 
    {
        global $DB;

        $conditions = array 
        (
            'coursework' => $studentWork->coursework,
            'student' => $studentWork->student
        );

        return $DB->get_field('coursework_students', 'id', $conditions);
    }

    private function is_student_work_exist(\stdClass $studentWork) : bool 
    {
        if(empty($studentWork->id)) return false;
        else return true;
    }

    private function display_missing_work_message() : void  
    {
        $attr = array
        (
            'style' => 'border: 1px solid #ffa500; 
                        background: #fffbd2;
                        padding: 10px;'
        );
        $text = get_string('impossible_return_to_work_state', 'coursework');
        echo \html_writer::tag('p', $text, $attr);
    }

    private function return_to_work_state(\stdClass $studentWork) : void 
    {
        global $DB;

        if($DB->update_record('coursework_students', $studentWork))
        {
            $this->send_notification_to_student($studentWork->student);
            $this->log_event($studentWork->student);
            $this->display_coursework_back_to_work_state_message();
        }
        else 
        {
            throw new \Exception('Student work wasn\'t returned to work state.');
        }
    }

    private function send_notification_to_student(int $studentId) : void 
    {
        global $USER;

        $cm = $this->cm;
        $course = $this->course;
        $userFrom = $USER;
        $userTo = cg::get_user($studentId); 
        $messageName = 'back_to_work_state';
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

    private function log_event($studentId)
    {
        $params = array
        (
            'relateduserid' => $studentId, 
            'context' => \context_module::instance($this->cm->id)
        );

        $event = \mod_coursework\event\student_coursework_back_to_work_state::create($params);
        $event->trigger();
    }

    private function display_coursework_back_to_work_state_message() : void  
    {
        $attr = array
        (
            'style' => 'border: 1px solid #5ac18e; 
                        background: #cbecc8;
                        padding: 10px;'
        );
        $text = get_string('student_coursework_back_to_work_state', 'coursework');
        echo \html_writer::tag('p', $text, $attr);
    }


}
