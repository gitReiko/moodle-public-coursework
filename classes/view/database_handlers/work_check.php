<?php

namespace Coursework\View\DatabaseHandlers;

use Coursework\Lib\Getters\StudentTaskGetter;
use Coursework\Lib\Getters\StudentsGetter as sg;
use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\CommonLib as cl;
use Coursework\Lib\Notification;

class WorkCheck 
{
    private $course;
    private $cm;

    private $work;

    function __construct(\stdClass $course, \stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->work = $this->get_work();
    }

    public function handle()
    {
        if($this->update_work_status())
        {
            if($this->is_new_status_need_to_fix())
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
            }

            $this->send_notification($this->work);
        }
    }

    private function get_work() : \stdClass 
    {
        $student = $this->get_student();
        $work = sg::get_students_work($this->cm->instance, $student);
        $work->status = $this->get_status();

        if($work->status == READY)
        {
            $work->grade = $this->get_grade();
        }

        $work->workstatuschangedate = time();
        return $work;
    }

    private function get_student() : int 
    {
        $student = optional_param(STUDENT, null, PARAM_INT);
        if(empty($student)) throw new Exception('Missing student id.');
        return $student;
    }

    private function get_status() : string 
    {
        $status = optional_param(STATUS, null, PARAM_TEXT);
        if(empty($status)) throw new Exception('Missing work status.');
        return $status;
    }

    private function get_grade() : int 
    {
        $grade = optional_param(GRADE, null, PARAM_INT);
        if(empty($grade)) throw new Exception('Missing work grade.');
        return $grade;
    }

    private function update_work_status()
    {
        global $DB;
        return $DB->update_record('coursework_students', $this->work);
    }

    private function is_new_status_need_to_fix() : bool 
    {
        if($this->work->status == NEED_TO_FIX)
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
        $grade->userid   = $this->work->student;
        $grade->rawgrade = $this->work->grade;
        $coursework = cg::get_coursework($this->cm->instance);
        coursework_grade_item_update($coursework, $grade);
    }

    private function update_user_task_sections_to_ready() : void 
    {
        $sections = $this->get_sections();

        foreach($sections as $section)
        {
            $sectionRow = $this->get_section($section->id);
            if($this->is_student_task_section_exist($section->id))
            {
                if($this->is_section_status_not_ready($section->id))
                {
                    $sectionRow->id = $this->get_section_id($section->id);
                    $this->update_section_in_database($sectionRow);
                }
            }
            else
            {
                $this->insert_section_to_database($sectionRow);
            }
        }
    }

    private function get_sections()
    {
        $ts = new StudentTaskGetter($this->cm->instance, $this->get_student());
        return $ts->get_sections();
    }

    private function is_student_task_section_exist(int $sectionId) : bool 
    {
        global $DB;
        $conditions = array('coursework' => $this->cm->instance,
                            'student' => $this->work->student,
                            'section' => $sectionId);
        return $DB->record_exists('coursework_sections_status', $conditions);
    }

    private function is_section_status_not_ready(int $sectionId) : bool 
    {
        global $DB;
        $sql = 'SELECT * 
                FROM {coursework_sections_status}
                WHERE coursework = ?
                AND student = ? 
                AND section = ? 
                AND status != ?';  
        $params = array($this->cm->instance, $this->work->student, $sectionId, READY);
        return $DB->record_exists_sql($sql, $params);
    }

    private function get_section(int $sectionId) : \stdClass 
    {
        $section = new \stdClass;
        $section->coursework = $this->cm->instance;
        $section->student = $this->work->student;
        $section->section = $sectionId;
        $section->status = READY;
        $section->timemodified = time();
        return $section;
    }

    private function insert_section_to_database(\stdClass $section) : void 
    {
        global $DB;
        $DB->insert_record('coursework_sections_status', $section);
    }

    private function get_section_id(int $sectionId) : int 
    {
        global $DB;
        $conditions = array('coursework' => $this->cm->instance,
                            'student' => $this->work->student,
                            'section' => $sectionId);
        return $DB->get_field('coursework_sections_status', 'id', $conditions);
    }
    
    private function update_section_in_database(\stdClass $section) : void 
    {
        global $DB;
        $DB->update_record('coursework_sections_status', $section);
    }

    private function send_notification(\stdClass $work) : void 
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

    private function log_event_teacher_sent_coursework_for_rework() : void 
    {
        $params = array
        (
            'relateduserid' => $this->work->student,
            'context' => \context_module::instance($this->cm->id)
        );
        
        $event = \mod_coursework\event\teacher_sent_coursework_for_rework::create($params);
        $event->trigger();
    }




}
