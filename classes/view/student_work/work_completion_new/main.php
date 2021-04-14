<?php

namespace Coursework\View\StudentsWork;

require_once 'work_completion.php';
require_once 'student_work_completion.php';
require_once 'manager_work_completion.php';
require_once 'teacher_work_completion.php';

use Coursework\Lib\CommonLib as cl;

class WorkCompletionMain 
{
    private $course;
    private $cm;
    private $studentId;

    function __construct(\stdClass $course, \stdClass $cm, int $studentId)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->studentId = $studentId;
    }

    public function get_page() : string 
    {
        global $USER;

        if(cl::is_user_manager($this->cm, $USER->id))
        {
            return $this->get_manager_page();
        }
        else if(cl::is_user_teacher($this->cm, $USER->id))
        {
            return $this->get_teacher_page();
        }
        else if(cl::is_user_student($this->cm, $USER->id))
        {
            return $this->get_student_page();
        }
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

