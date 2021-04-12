<?php

namespace Coursework\Lib\Getters;

use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\Enums as enum;

class StudentTaskGetter
{
    private $courseworkId;
    private $studentId;

    private $taskId;
    private $sections;

    function __construct(int $courseworkId, int $studentId)
    {
        $this->courseworkId = $courseworkId;
        $this->studentId = $studentId;

        $this->init_task();
        $this->init_sections();
    }

    public function get_task_id() 
    {
        return $this->taskId;
    }

    public function get_sections()
    {
        return $this->sections;
    }

    private function init_task()
    {
        global $DB;
        $where = array(
            'coursework' => $this->courseworkId,
            'student' => $this->studentId
        );
        $this->taskId = $DB->get_field('coursework_students', 'task', $where);
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
        $where = array('task' => $this->taskId);
        $orderBy = 'listposition';
        return $DB->get_records($table, $where, $orderBy);
    }

    private function add_status_to_sections($sections)
    {
        foreach($sections as $section)
        {
            $state = $this->get_section_status($section);

            if($this->is_section_status_exist($state))
            {
                $section->status = $state->status;
            }
            else 
            {
                $section->status = enum::NOT_READY;
            }
        }

        return $sections;
    }

    private function get_section_status(\stdClass $section) 
    {
        global $DB;
        $table = 'coursework_sections_status';
        $where = array(
            'coursework' => $this->courseworkId,
            'student' => $this->studentId,
            'section' => $section->id
        );
        return $DB->get_record($table, $where);
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


}
