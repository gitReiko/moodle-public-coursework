<?php

namespace Coursework\Config\DistributeToLeaders;

use Coursework\ClassesLib\StudentsMassActions\Lib as massLib;
use Coursework\Lib\Getters\TeachersGetter as tg;

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
        $this->leader = $this->get_leader();
        $this->expandQuota = $this->get_expand_quota();
    }

    public function execute() 
    {
        foreach($this->students as $student)
        {
            $this->distribute_student($student);
        }
    }

    private function get_expand_quota() : bool 
    {
        $expandQuota = optional_param(StudentsDistribution::EXPAND_QUOTA, false, PARAM_TEXT);

        if($expandQuota) return true;
        else return false;
    }

    private function get_leader() : \stdClass 
    {
        $leader = new \stdClass;
        $leader->id = optional_param(TEACHER, null, PARAM_INT);
        $leader->course = optional_param(COURSE, null, PARAM_INT);

        if(empty($leader->id)) throw new \Exception(get_string('e-sd-ev:missing_leader_id', 'coursework'));
        if(empty($leader->course)) throw new \Exception(get_string('e-sd-ev:missing_course_id', 'coursework'));

        $leader->remainingQuota = tg::get_available_leader_quota_in_course(
            $this->cm, 
            $leader->id, 
            $leader->course
        );

        return $leader;
    }

    private function distribute_student(\stdClass $student) : void
    {
        global $DB;

        if($this->is_student_dont_distributed($student->id))
        {
            if($this->expandQuota && ($this->leader->remainingQuota == 0))
            {
                $this->increment_leader_quota();
            }

            if($this->leader->remainingQuota > 0)
            {
                $dbStudent = $this->get_student($student->id);
                $DB->insert_record('coursework_students', $dbStudent, false);

                $attr = array('class' => 'green-message');
                $text = get_string('student_successfully_distributed', 'coursework', $student);
                echo \html_writer::tag('span', $text, $attr);
    
                $this->leader->remainingQuota--;
            }
            else
            {
                $attr = array('class' => 'red-message');
                $text = get_string('not_enough_quota_for_distribution', 'coursework', $student);
                echo \html_writer::tag('span', $text, $attr);
            }

        }
        else 
        {
            $attr = array('class' => 'red-message');
            $text = get_string('student_redistribution_impossible', 'coursework', $student);
            echo \html_writer::tag('span', $text, $attr);
        }
    }

    private function is_student_dont_distributed(int $studentid) : bool 
    {
        global $DB;
        $conditions = array('coursework'=>$this->cm->instance, 'student'=>$studentid, 'teacher'=>$this->leader->id);

        if($DB->record_exists('coursework_students', $conditions)) return false;
        else return true;
    }

    private function increment_leader_quota() : void 
    {
        global $DB;
        $sql = "UPDATE {coursework_teachers} SET `quota` = `quota` + 1 WHERE `coursework`= ? AND teacher = ? AND course = ?";
        $conditions = array($this->cm->instance, $this->leader->id, $this->leader->course);

        $DB->execute($sql, $conditions);

        $this->leader->remainingQuota++;
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


}

