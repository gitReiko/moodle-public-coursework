<?php

namespace Coursework\View\DatabaseHandlers;

use Coursework\View\DatabaseHandlers\Main as MainDB;
use Coursework\Lib\Getters\StudentTaskGetter;
use Coursework\Lib\Getters\StudentsGetter as sg;
use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\CommonLib as cl;
use Coursework\Lib\Notification;
use Coursework\Lib\Enums;

class CheckWork 
{
    private $course;
    private $cm;

    private $student;
    private $studentWork;
    private $newCourseworkState;

    function __construct(\stdClass $course, \stdClass $cm)
    {
        $this->cm = $cm;
        $this->course = $course;

        $studentId = $this->get_student_id();

        $this->student = sg::get_student_with_his_work(
            $this->cm->instance, 
            $studentId
        );

        $this->studentWork = sg::get_student_work(
            $this->cm->instance, 
            $studentId
        );
        $this->studentWork->grade = $this->get_grade();

        $this->newCourseworkState = $this->get_new_coursework_state();
    }

    public function handle()
    {
        if($this->is_new_coursework_state_ready())
        {
            if($this->is_coursework_regrading())
            {
                $this->save_grade_in_gradebook();
                $this->update_grade_in_coursework_students_table();

                $text = get_string('work_regrade_message','coursework');
                $this->send_notification($this->studentWork, $text);

                $this->log_event_teacher_regraded_coursework();
            }
            else 
            {
                if($this->add_new_coursework_status())
                {
                    $this->save_grade_in_gradebook();
                    $this->update_grade_in_coursework_students_table();

                    $text = get_string('work_check_message','coursework');
                    $this->send_notification($this->studentWork, $text);

                    if(cl::is_coursework_use_task($this->cm->instance))
                    {
                        $this->set_sections_status_ready();
                    }

                    $this->log_event_teacher_accepted_and_graded_coursework();
                }
            }
        }
        // sent for rework
        else 
        {
            if($this->add_new_coursework_status())
            {
                $this->save_grade_in_gradebook();
                $this->update_grade_in_coursework_students_table();

                $text = get_string('work_back_to_rework','coursework');
                $this->send_notification($this->studentWork, $text);

                $this->log_event_teacher_sent_coursework_for_rework();
            }
        }
    }

    private function get_student_id() : int 
    {
        $studentId = optional_param(MainDB::STUDENT, null, PARAM_INT);
        if(empty($studentId)) throw new \Exception('Missing student id.');
        return $studentId;
    }

    private function get_grade()  
    {
        return optional_param(MainDB::GRADE, 0, PARAM_INT);
    }

    private function get_new_coursework_state()
    {
        $state = new \stdClass;
        $state->coursework = $this->studentWork->coursework;
        $state->student = $this->studentWork->student;
        $state->type = Enums::COURSEWORK;
        $state->instance = $this->studentWork->coursework;
        $state->status = $this->get_status();
        $state->changetime = time();

        return $state;
    }

    private function get_status() : string 
    {
        $status = optional_param(MainDB::STATUS, null, PARAM_TEXT);
        if(empty($status)) throw new \Exception('Missing work status.');
        return $status;
    }

    private function is_new_coursework_state_ready() : bool 
    {
        if($this->newCourseworkState->status == Enums::READY)
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    private function is_coursework_regrading() : bool 
    {
        if(empty($this->student->grade))
        {
            return false;
        }
        else 
        {
            return true;
        }
    }

    private function save_grade_in_gradebook() : void 
    {
        $grade = new \stdClass;
        $grade->userid   = $this->studentWork->student;
        $grade->rawgrade = $this->studentWork->grade;
        $coursework = cg::get_coursework($this->cm->instance);
        coursework_grade_item_update($coursework, $grade);
    }

    private function update_grade_in_coursework_students_table()
    {
        global $DB;
        $DB->update_record('coursework_students', $this->studentWork);
    }

    private function send_notification(\stdClass $work, $notifyText) : void 
    {
        $cm = $this->cm;
        $course = $this->course;
        $userFrom = cg::get_user($work->teacher);
        $userTo = cg::get_user($work->student); 
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

    private function log_event_teacher_regraded_coursework() : void 
    {
        $params = array
        (
            'relateduserid' => $this->studentWork->student,
            'context' => \context_module::instance($this->cm->id)
        );
        
        $event = \mod_coursework\event\teacher_regraded_coursework::create($params);
        $event->trigger();
    }

    private function add_new_coursework_status()
    {
        global $DB;
        return $DB->insert_record('coursework_students_statuses', $this->newCourseworkState);
    }

    private function set_sections_status_ready() : void 
    {
        $sections = $this->get_sections();

        foreach($sections as $section)
        {
            $section = $this->get_section_new_status($section);
            $this->add_new_section_status($section);
        }
    }

    private function get_sections()
    {
        $ts = new StudentTaskGetter($this->cm->instance, $this->get_student());
        return $ts->get_sections();
    }

    private function get_section_new_status(int $section)
    {
        $state = new \stdClass;
        $state->coursework = $this->studentWork->coursework;
        $state->student = $this->studentWork->student;
        $state->type = Enums::SECTION;
        $state->instance = $section->id;
        $state->status = Enums::READY;
        $state->changetime = time();

        return $state;
    }

    private function add_new_section_status(\stdClass $newState) : void 
    {
        global $DB;
        $DB->insert_record('coursework_students_statuses', $newState);
    }

    private function log_event_teacher_accepted_and_graded_coursework() : void 
    {
        $params = array
        (
            'relateduserid' => $this->studentWork->student,
            'context' => \context_module::instance($this->cm->id)
        );
        
        $event = \mod_coursework\event\teacher_accepted_and_graded_coursework::create($params);
        $event->trigger();
    }

    private function log_event_teacher_sent_coursework_for_rework() : void 
    {
        $params = array
        (
            'relateduserid' => $this->studentWork->student,
            'context' => \context_module::instance($this->cm->id)
        );
        
        $event = \mod_coursework\event\teacher_sent_coursework_for_rework::create($params);
        $event->trigger();
    }

}
