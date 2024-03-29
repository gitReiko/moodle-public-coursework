<?php

namespace Coursework\Config\DistributeToLeaders;

use Coursework\Classes\Lib\StudentsMassActions\Lib as massLib;
use Coursework\Lib\Getters\TeachersGetter as tg;
use Coursework\Lib\Feedbacker;

class Database 
{
    private $course;
    private $cm;

    private $students;
    private $leader;
    private $expandQuota;

    function __construct(\stdClass $course, \stdClass $cm) 
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->students = massLib::get_distribute_students();
        $this->leader = $this->get_leader_from_request();
        $this->expandQuota = $this->get_expand_quota();
    }

    public function execute() : string 
    {
        $feedback = '';

        if($this->expandQuota)
        {
            $feedbackItem = $this->increase_leader_quota();
            $feedback.= Feedbacker::add_feedback_to_post($feedbackItem);
        }

        $this->leader->remainingQuota = $this->get_leader_remaining_quota();

        foreach($this->students as $student)
        {
            $feedbackItem = $this->distribute_student($student);
            $feedback.= Feedbacker::add_feedback_to_post($feedbackItem);
        }

        return $feedback;
    }

    private function increase_leader_quota() : \stdClass  
    {
        $leader = $this->get_leader();

        if($this->is_leader_exist())
        {
            return $this->update_leader_quota($leader);
        }
        else 
        {
            return $this->create_leader_with_quota($leader);
        }
    }

    private function get_leader()
    {
        $leader = new \stdClass;
        $leader->coursework = $this->cm->instance;
        $leader->teacher = $this->leader->id;
        $leader->course = $this->leader->course;

        return $leader;
    }

    private function is_leader_exist() : bool 
    {
        global $DB;
        $where = array(
            'coursework' => $this->cm->instance,
            'teacher' => $this->leader->id,
            'course' => $this->leader->course
        );
        return $DB->record_exists('coursework_teachers', $where);
    }

    private function get_leader_from_database() : \stdClass
    {
        global $DB;
        $where = array(
            'coursework' => $this->cm->instance,
            'teacher' => $this->leader->id,
            'course' => $this->leader->course
        );
        return $DB->get_record('coursework_teachers', $where);
    }

    private function get_quota_increase()
    {
        $studentsCount = count($this->students);
        $remainingQuota = $this->get_leader_remaining_quota();

        return abs($remainingQuota - $studentsCount);
    }

    private function create_leader_with_quota(\stdClass $leader) : \stdClass  
    {
        global $DB;
        $leader->quota = $this->get_quota_increase();

        if($DB->insert_record('coursework_teachers', $leader, false))
        {
            $this->log_event_leader_quota_increased();
            return $this->get_success_quota_feedback();
        }
        else 
        {
            return $this->get_fail_quota_feedback();
        }
    }

    private function update_leader_quota(\stdClass $leader) : \stdClass  
    {
        global $DB;
        $dbLeader = $this->get_leader_from_database();
        $leader->id = $dbLeader->id;
        $leader->quota = $dbLeader->quota + $this->get_quota_increase();

        if($DB->update_record('coursework_teachers', $leader))
        {
            $this->log_event_leader_quota_increased();
            return $this->get_success_quota_feedback();
        }
        else 
        {
            return $this->get_fail_quota_feedback();
        }
    }

    private function get_expand_quota() : bool 
    {
        $expandQuota = optional_param(Main::EXPAND_QUOTA, false, PARAM_TEXT);

        if($expandQuota) return true;
        else return false;
    }

    private function get_leader_from_request() : \stdClass 
    {
        $leader = new \stdClass;
        $leader->id = optional_param(Main::TEACHER, null, PARAM_INT);
        $leader->course = optional_param(Main::COURSE, null, PARAM_INT);

        if(empty($leader->id)) throw new \Exception('Missing leader id.');
        if(empty($leader->course)) throw new \Exception('Missing course id.');

        return $leader;
    }

    private function get_leader_remaining_quota()
    {
        return tg::get_available_leader_quota_in_course(
            $this->cm, 
            $this->leader->id, 
            $this->leader->course
        );
    }

    private function distribute_student(\stdClass $student) : \stdClass 
    {
        global $DB;

        if($this->is_student_dont_distributed($student->id))
        {
            if($this->leader->remainingQuota > 0)
            {
                $dbStudent = $this->get_student($student->id);

                if($DB->insert_record('coursework_students', $dbStudent, false))
                {
                    $this->log_event_student_distributed_to_teacher($student->id);
                    $this->leader->remainingQuota--;
                    return $this->get_success_student_feedback($student);
                }
            }
            else
            {
                return $this->get_fail_student_feedback($student);
            }
        }
        else 
        {
            return $this->get_fail_redistribution_feedback($student);
        }
    }

    private function is_student_dont_distributed(int $studentid) : bool 
    {
        global $DB;
        $conditions = array('coursework'=>$this->cm->instance, 'student'=>$studentid, 'teacher'=>$this->leader->id);

        if($DB->record_exists('coursework_students', $conditions)) return false;
        else return true;
    }

    private function get_student(int $studentid) : \stdClass 
    {
        $student = new \stdClass;
        $student->coursework = $this->cm->instance;
        $student->student = $studentid;
        $student->teacher = $this->leader->id;
        $student->course = $this->leader->course;

        return $student;
    }

    private function log_event_leader_quota_increased() : void 
    {
        $params = array
        (
            'relateduserid' => $this->leader->id, 
            'context' => \context_module::instance($this->cm->id)
        );
        
        $event = \mod_coursework\event\leader_quota_increased::create($params);
        $event->trigger();
    }

    private function get_success_quota_feedback() : \stdClass  
    {
        $text = get_string('leader_quota_increased', 'coursework');
        return Feedbacker::get_success_feedback($text);
    }

    private function get_fail_quota_feedback() : \stdClass  
    {
        $text = 'Leader quota not increased.';
        return Feedbacker::get_fail_feedback($text);
    }

    private function log_event_student_distributed_to_teacher(int $studentId) : void 
    {
        $params = array
        (
            'relateduserid' => $studentId, 
            'context' => \context_module::instance($this->cm->id)
        );
        
        $event = \mod_coursework\event\student_distributed_to_teacher::create($params);
        $event->trigger();
    }

    private function get_success_student_feedback(\stdClass $student) : \stdClass  
    {
        $text = get_string('student_successfully_distributed', 'coursework', $student);
        return Feedbacker::get_success_feedback($text);
    }

    private function get_fail_student_feedback(\stdClass $student) : \stdClass  
    {
        $text = get_string('not_enough_quota_for_distribution', 'coursework', $student);
        return Feedbacker::get_fail_feedback($text);
    }

    private function get_fail_redistribution_feedback(\stdClass $student) : \stdClass  
    {
        $text = get_string('student_redistribution_impossible', 'coursework', $student);
        return Feedbacker::get_fail_feedback($text);
    }


}

