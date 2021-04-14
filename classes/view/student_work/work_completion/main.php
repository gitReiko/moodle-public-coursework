<?php

require_once 'new_work_completion.php';
require_once 'work_completion.php';
require_once 'student_work_completion.php';
require_once 'manager_work_completion.php';
require_once 'teacher_work_completion.php';

use Coursework\View\StudentWork\NewWorkCompletion;

use coursework_lib as lib;

class WorkCompletionMain 
{
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

        $new = new NewWorkCompletion($this->course, $this->cm, $this->studentId);
        $page = $new->get_page();

        if(lib\is_user_student($this->cm, $USER->id))
        {
            $page.= $this->get_student_page();
        }
        else if(lib\is_user_teacher($this->cm, $USER->id))
        {
            $page.= $this->get_teacher_page();
        }
        else if(lib\is_user_manager($this->cm, $USER->id))
        {
            $page.= $this->get_manager_page();
        }

        return $page;
    }


    private function get_student_page() : string 
    {
        global $USER;
        $taskAssign = new StudentWorkComplition($this->course, $this->cm, $USER->id);
        return $taskAssign->get_page();
    }

    private function get_manager_page() : string 
    {
        global $USER;
        $taskAssign = new ManagerWorkComplition($this->course, $this->cm, $this->studentId);
        return $taskAssign->get_page();
    }

    private function get_teacher_page() : string 
    {
        global $USER;
        $taskAssign = new TeacherWorkComplition($this->course, $this->cm, $this->studentId);
        return $taskAssign->get_page();
    }




}

