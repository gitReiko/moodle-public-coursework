<?php

namespace Coursework\View\DatabaseHandlers;

use Coursework\Lib\Database\AddNewStatusToAllSections;
use Coursework\Lib\Database\AddNewStudentWorkStatus;
use Coursework\View\DatabaseHandlers\Main as MainDB;
use Coursework\Lib\Getters\StudentsGetter as sg;
use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\Getters\UserGetter as ug;
use Coursework\Lib\Getters\StudentTaskGetter;
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
    }

    public function handle()
    {
        if($this->is_new_coursework_state_ready())
        {
            echo ' <p>state ready</p>';
            if($this->is_coursework_regrading())
            {
                echo ' <p>regrading</p>';
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
                        $this->set_ready_status_to_all_sections();
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

    private function is_new_coursework_state_ready() : bool 
    {
        if($this->get_status() == Enums::READY)
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    private function get_status() : string 
    {
        $status = optional_param(MainDB::STATUS, null, PARAM_TEXT);
        if(empty($status)) throw new \Exception('Missing work status.');
        return $status;
    }

    private function is_coursework_regrading() : bool 
    {
        if($this->student->latestStatus == Enums::READY)
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
        $userFrom = ug::get_user($work->teacher);
        $userTo = ug::get_user($work->student); 
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
        $addNewStatus = new AddNewStudentWorkStatus(
            $this->studentWork->coursework, 
            $this->studentWork->student, 
            $this->get_status()
        );
        return $addNewStatus->execute();
    }

    private function set_ready_status_to_all_sections() : void 
    {
        $addNewStatus = new AddNewStatusToAllSections(
            $this->studentWork,
            $this->get_unchecked_sections(),
            Enums::READY
        );
        $addNewStatus->execute();
    }

    private function get_unchecked_sections()
    {
        $sections = $this->get_all_student_task_sections();
        $sections = $this->filter_unchecked_sections($sections);
        return $sections;
    }

    private function get_all_student_task_sections()
    {
        $ts = new StudentTaskGetter($this->cm->instance, $this->get_student_id());
        return $ts->get_sections();
    }

    private function filter_unchecked_sections($sections)
    {
        $uncheked = array();

        foreach($sections as $section)
        {
            if($section->latestStatus == Enums::READY)
            {
                continue;
            }

            $uncheked[] = $section;
        }

        return $uncheked;
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
