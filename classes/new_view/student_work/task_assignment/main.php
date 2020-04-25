<?php

require_once 'task_issuance.php';
require_once 'assign_custom_task.php';
require_once 'assign_new_task.php';

use coursework_lib as lib;

class TaskAssignmentMain 
{
    const ASSIGN_PAGE = 'assign_page';
    const TEMPLATE_CORRECT = 'template_correct';
    const NEW_TASK = 'new_task';

    private $course;
    private $cm;
    private $studentId;

    function __construct(stdClass $course, stdClass $cm, int $studentId)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->studentId = $studentId;
    }

    public function get_page() : string 
    {
        global $USER;
        if(lib\is_user_teacher($this->cm, $USER->id))
        {
            $page = optional_param(self::ASSIGN_PAGE, null, PARAM_TEXT);

            if($page == self::TEMPLATE_CORRECT)
            {
                return 'template_correct';
            }
            else if($page == self::NEW_TASK)
            {
                return $this->get_create_new_task_page();
            }
            else 
            {
                return $this->get_task_issuance_page();
            }
        }
        else 
        {
            return 'student page';
        }
    }

    private function get_task_issuance_page() : string 
    {
        $issuance = new TaskIssuance($this->course, $this->cm, $this->studentId);
        return $issuance->get_page();
    }

    private function get_create_new_task_page() : string 
    {
        $newTask = new AssignNewTask($this->course, $this->cm, $this->studentId);
        return $newTask->get_page();
    }




}