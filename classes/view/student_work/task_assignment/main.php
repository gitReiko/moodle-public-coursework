<?php

namespace Coursework\View\StudentWork\TaskAssignment;

require_once 'task_assign_methods.php';
require_once 'assign_custom_task.php';
require_once 'assign_new_task.php';
require_once 'correct_task.php';

use Coursework\View\StudentWork\Locallib as locallib;
use Coursework\View\StudentWork\Components as c;
use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\Getters\StudentsGetter as sg;

class Main 
{
    const ASSIGN_PAGE = 'assign_page';
    const TEMPLATE_CORRECT = 'template_correct';
    const NEW_TASK = 'new_task';

    private $course;
    private $cm;
    private $studentId;
    private $studentWork;

    function __construct(\stdClass $course, \stdClass $cm, int $studentId)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->studentId = $studentId;
        $this->studentWork = sg::get_student_with_his_work($cm->instance, $studentId);
    }

    public function get_page() : string 
    {
        $page = $this->get_info_block();
        $page.= $this->get_guidelines_block();
        $page.= $this->get_chat_block();
        $page.= $this->get_task_assignment_block();
        $page.= $this->get_navigation_block();

        return $page;
    }

    private function get_info_block() : string 
    {
        $info = new c\Info($this->course, $this->cm, $this->studentId);
        return $info->get_component();
    }

    private function get_guidelines_block() : string 
    {
        $guidelines = new c\Guidelines($this->course, $this->cm, $this->studentId);
        return $guidelines->get_component();
    }

    private function get_chat_block() : string 
    {
        $chat = new c\Chat($this->course, $this->cm, $this->studentId);
        return $chat->get_component();
    }

    private function get_task_assignment_block() : string 
    {
        $header = get_string('task_template', 'coursework');
        $content = $this->get_task_assignment_content();
        
        $themeSelection = new c\Container(
            $this->course, 
            $this->cm, 
            $this->studentId,
            $header,
            $content
        );
        return $themeSelection->get_component();
    }

    private function get_task_assignment_content() : string 
    {
        if(locallib::is_user_teacher($this->studentWork))
        {
            return $this->get_teacher_task_assignment_block();
        }
        else 
        {
            return $this->get_waiting_for_task_assignment_message();
        }
    }

    private function get_teacher_task_assignment_block()
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
            return $this->get_task_assign_methods();
        }
    }

    private function get_correct_task_page() : string 
    {
        $correctTask = new CorrectTask($this->course, $this->cm, $this->studentId);
        return $correctTask->get_page();
    }

    private function get_create_new_task_page() : string 
    {
        $newTask = new AssignNewTask($this->course, $this->cm, $this->studentId);
        return $newTask->get_page();
    }

    private function get_task_assign_methods() : string 
    {
        $taskAssignMethods = new TaskAssignMethods($this->cm, $this->studentId);
        return $taskAssignMethods->get();
    }

    protected function get_waiting_for_task_assignment_message() : string 
    {
        $text = get_string('waiting_for_task_assignment', 'coursework');
        return \html_writer::tag('p', $text);
    }

    private function get_navigation_block() : string 
    {
        $chat = new c\Navigation($this->course, $this->cm, $this->studentId);
        return $chat->get_component();
    }


}