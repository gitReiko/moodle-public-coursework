<?php

namespace Coursework\Lib\Getters;

use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\Enums as enum;

class StudentTaskGetter
{
    private $courseworkId;
    private $studentId;

    private $studentWork;
    private $task;
    private $sections;

    function __construct(int $courseworkId, int $studentId)
    {
        $this->courseworkId = $courseworkId;
        $this->studentId = $studentId;

        $this->init_student_work();
        $this->init_task();

        if(!empty($this->task->id))
        {
            $this->init_sections();
        }
    }

    public function get_task_id() 
    {
        return $this->task->id;
    }

    public function get_task()
    {
        return $this->task;
    }

    public function get_sections()
    {
        return $this->sections;
    }

    private function init_student_work()
    {
        global $DB;
        $where = array(
            'coursework' => $this->courseworkId,
            'student' => $this->studentId
        );
        $this->studentWork = $DB->get_record('coursework_students', $where);
    }

    function init_task()
    {
        global $DB;
        $where = array('id' => $this->studentWork->task);
        $this->task = $DB->get_record('coursework_tasks', $where);
    }

    private function init_sections()
    {
        $sections = $this->get_task_sections();
        $sections = $this->add_status_to_sections($sections);

        $this->sections = $sections;
    }

    private function get_task_sections() 
    {
        global $DB;
        $table = 'coursework_tasks_sections';
        $where = array('task' => $this->task->id);
        $orderBy = 'listposition';
        return $DB->get_records($table, $where, $orderBy);
    }

    private function add_status_to_sections($sections)
    {
        foreach($sections as $section)
        {
            $state = $this->get_section_latest_status($section);

            if($this->is_section_status_exist($state))
            {
                $section->latestStatus = $state->status;
                $section->statusChangeTime = $state->changetime;
            }
            else 
            {
                $section->latestStatus = enum::STARTED;
                $section->statusChangeTime = $this->get_coursework_task_receiving_date();
            }
        }

        return $sections;
    }

    private function get_section_latest_status(\stdClass $section) 
    {
        global $DB;

        $sql = 'SELECT css.status, css.changetime 
                FROM {coursework_students_statuses} AS css 
                WHERE css.coursework = ? 
                AND css.student = ? 
                AND css.type = ? 
                AND css.instance = ? 
                ORDER BY css.changetime ';
        
        $params = array(
            $this->courseworkId,
            $this->studentId,
            enum::SECTION,
            $section->id
        );

        $states = $DB->get_records_sql($sql, $params);

        usort($states, function($a, $b)
        {
            $a = intval($a->changetime);
            $b = intval($b->changetime);
            if ($a == $b) { return 0; }
            return ($a < $b) ? -1 : 1;
        });

        return end($states);
    }

    private function is_section_status_exist($state) : bool 
    {
        if(empty($state->status))
        {
            return false;
        }
        else 
        {
            return true;
        }
    }

    private function get_coursework_task_receiving_date()
    {
        global $DB;
        $sql = 'SELECT changetime 
                FROM {coursework_students_statuses} 
                WHERE coursework = ?
                AND student = ? 
                AND type = `coursework`
                AND instance = ? 
                AND `status` = ? 
                GROUP BY student 
                HAVING changetime = MAX(changetime)';
        $params = array(
            $this->studentWork->coursework, 
            $this->studentWork->student, 
            $this->studentWork->coursework,
            enum::TASK_RECEIPT
        );
        return $DB->get_field_sql($sql, $params);
    }


}
