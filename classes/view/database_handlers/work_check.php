<?php

namespace Coursework\View\DatabaseHandlers;

use Coursework\View\DatabaseHandlers\Main as MainDB;
use Coursework\Lib\Getters\StudentTaskGetter;
use Coursework\Lib\Getters\StudentsGetter as sg;
use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\CommonLib as cl;
use Coursework\Lib\Notification;
use Coursework\Lib\Enums;

class WorkCheck 
{
    private $course;
    private $cm;

    private $student;

    function __construct(\stdClass $course, \stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->student = $this->get_student();
    }

    public function handle()
    {
        if($this->add_new_coursework_status())
        {
            if($this->is_new_status_returned_for_rework())
            {
                $this->log_event_teacher_sent_coursework_for_rework();
            }
            else 
            {
                $this->save_grade_in_gradebook();
    
                if(cl::is_coursework_use_task($this->cm->instance))
                {
                    $this->update_user_task_sections_to_ready();
                }

                if($this->is_coursework_already_graded())
                {
                    $this->log_event_teacher_regraded_coursework();
                }
                else 
                {
                    $this->log_event_teacher_accepted_and_graded_coursework();
                }
            }

            $this->send_notification($this->student);
        }
    }

    private function get_student() : \stdClass 
    {
        $student = $this->get_student_from_request();
        $student = sg::get_student_with_his_work($this->cm->instance, $student);

        if(empty($student->grade))
        {
            $student->emptyGrade = true;
        }
        else
        {
            $student->emptyGrade = false;
        }

        if($this->get_status() == MainDB::READY)
        {
            $student->grade = $this->get_grade();
        }

        return $student;
    }

    private function get_student_from_request() : int 
    {
        $student = optional_param(MainDB::STUDENT, null, PARAM_INT);
        if(empty($student)) throw new Exception('Missing student id.');
        return $student;
    }

    private function get_status() : string 
    {
        $status = optional_param(MainDB::STATUS, null, PARAM_TEXT);
        if(empty($status)) throw new Exception('Missing work status.');
        return $status;
    }

    private function get_grade() : int 
    {
        $grade = optional_param(MainDB::GRADE, null, PARAM_INT);
        if(empty($grade)) throw new Exception('Missing work grade.');
        return $grade;
    }

    private function add_new_coursework_status()
    {
        global $DB;
        $status = $this->get_returned_for_rework_status();
        return $DB->insert_record('coursework_students_statuses', $status);
    }

    private function get_returned_for_rework_status()
    {
        $state = new \stdClass;
        $state->coursework = $this->student->coursework;
        $state->student = $this->student->id;
        $state->type = Enums::COURSEWORK;
        $state->instance = $this->student->coursework;
        $state->status = $this->get_status();
        $state->changetime = time();

        return $state;
    }

    private function is_new_status_returned_for_rework() : bool 
    {
        if($this->student->latestStatus == MainDB::RETURNED_FOR_REWORK)
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    private function save_grade_in_gradebook() : void 
    {
        $grade = new \stdClass;
        $grade->userid   = $this->student->id;
        $grade->rawgrade = $this->student->grade;
        $coursework = cg::get_coursework($this->cm->instance);
        coursework_grade_item_update($coursework, $grade);
    }

    private function update_user_task_sections_to_ready() : void 
    {
        $sections = $this->get_sections();

        foreach($sections as $section)
        {
            $this->add_section_status_in_database($sectionRow);
        }
    }

    private function get_sections()
    {
        $ts = new StudentTaskGetter($this->cm->instance, $this->get_student());
        return $ts->get_sections();
    }

    private function add_section_status_in_database(\stdClass $section) : void 
    {
        global $DB;
        $section->status = MainDB::READY;
        $section->timemodified = time();
        $DB->insert_record('coursework_students_statuses', $section);
    }

    private function send_notification(\stdClass $student) : void 
    {
        $cm = $this->cm;
        $course = $this->course;
        $userFrom = cg::get_user($student->teacher);
        $userTo = cg::get_user($student->id); 
        $messageName = 'workcheck';
        $messageText = get_string('work_check_message','coursework');

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

    private function log_event_teacher_sent_coursework_for_rework() : void 
    {
        $params = array
        (
            'relateduserid' => $this->student->id,
            'context' => \context_module::instance($this->cm->id)
        );
        
        $event = \mod_coursework\event\teacher_sent_coursework_for_rework::create($params);
        $event->trigger();
    }

    private function is_coursework_already_graded() : bool 
    {
        if($this->student->emptyGrade)
        {
            return false;
        }
        else 
        {
            return true;
        }
    }

    private function log_event_teacher_accepted_and_graded_coursework() : void 
    {
        $params = array
        (
            'relateduserid' => $this->student->id,
            'context' => \context_module::instance($this->cm->id)
        );
        
        $event = \mod_coursework\event\teacher_accepted_and_graded_coursework::create($params);
        $event->trigger();
    }

    private function log_event_teacher_regraded_coursework() : void 
    {
        $params = array
        (
            'relateduserid' => $this->student->id,
            'context' => \context_module::instance($this->cm->id)
        );
        
        $event = \mod_coursework\event\teacher_regraded_coursework::create($params);
        $event->trigger();
    }




}
