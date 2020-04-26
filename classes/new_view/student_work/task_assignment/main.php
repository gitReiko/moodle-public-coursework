<?php

require_once 'task_issuance.php';
require_once 'manager_task_issuance.php';
require_once 'teacher_task_issuance.php';
require_once 'student_task_issuance.php';
require_once 'assign_custom_task.php';
require_once 'assign_new_task.php';
require_once 'correct_task.php';

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
                return $this->get_correct_task_page();
            }
            else if($page == self::NEW_TASK)
            {
                return $this->get_create_new_task_page();
            }
            else 
            {
                return $this->get_teacher_task_issuance_page();
            }
        }
        else 
        {
            if(lib\is_user_manager($this->cm, $USER->id))
            {
                return $this->get_manager_task_issuance_page();
            }
            else 
            {
                return $this->get_student_task_issuance_page();
            }
        }
    }

    private function get_teacher_task_issuance_page() : string 
    {
        $issuance = new TeacherTaskIssuance($this->course, $this->cm, $this->studentId);
        return $issuance->get_page();
    }

    private function get_student_task_issuance_page() : string 
    {
        global $USER;
        $issuance = new StudentTaskIssuance($this->course, $this->cm, $USER->id);
        return $issuance->get_page();
    }

    private function get_manager_task_issuance_page() : string 
    {
        $issuance = new ManagerTaskIssuance($this->course, $this->cm, $this->studentId);
        return $issuance->get_page();
    }

    private function get_create_new_task_page() : string 
    {
        $newTask = new AssignNewTask($this->course, $this->cm, $this->studentId);
        return $newTask->get_page();
    }

    private function get_correct_task_page() : string 
    {
        $correctTask = new CorrectTask($this->course, $this->cm, $this->studentId);
        return $correctTask->get_page();
    }




}