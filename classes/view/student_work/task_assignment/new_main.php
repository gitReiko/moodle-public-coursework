<?php

namespace Coursework\View\StudentWork\TaskAssignment;

require_once 'new_assign_custom_task.php';
require_once 'new_assign_new_task.php';
require_once 'new_correct_task.php';

use Coursework\View\StudentsWork\Locallib as locallib;
use Coursework\View\StudentsWork\Components as c;
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
        $this->studentWork = sg::get_students_work($cm->instance, $studentId);
    }

    public function get_page() : string 
    {
        $page = cg::get_page_header($this->cm);
        $page.= $this->get_info_block();
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
            return $this->get_assign_task_buttons();
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

    private function get_assign_task_buttons() : string 
    {
        $text = $this->get_use_template_button();
        $text.= $this->get_correct_template_button();
        $text.= $this->get_create_task_button();
        return \html_writer::tag('p', $text);
    }

    private function get_use_template_button() : string 
    {
        $btn = $this->get_neccessary_form_params();

        $attr = array(
            'type' => 'hidden',
            'name' => DB_EVENT,
            'value' => \ViewDatabaseHandler::USE_TASK_TEMPLATE
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $text = get_string('use_task_template', 'coursework');
        $btn.= \html_writer::tag('button', $text);

        $attr = array(
            'method' => 'post',
            'style' => 'display: inline-block'
        );
        return \html_writer::tag('form', $btn, $attr);
    }

    private function get_neccessary_form_params() : string 
    {
        $attr = array(
            'type' => 'hidden',
            'name' => ID,
            'value' => $this->cm->id
        );
        $params = \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => STUDENT.ID,
            'value' => $this->studentId
        );
        $params.= \html_writer::empty_tag('input', $attr);

        return $params;
    }

    private function get_correct_template_button() : string 
    {
        $btn = $this->get_neccessary_form_params();

        $attr = array(
            'type' => 'hidden', 
            'name' => \ViewMain::GUI_EVENT, 
            'value' => \ViewMain::USER_WORK 
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden', 
            'name' => \TaskAssignmentMain::ASSIGN_PAGE, 
            'value' => \TaskAssignmentMain::TEMPLATE_CORRECT 
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $text = get_string('correct_template', 'coursework');
        $btn.= \html_writer::tag('button', $text);

        $attr = array(
            'method' => 'post',
            'style' => 'display: inline-block'
        );
        return \html_writer::tag('form', $btn, $attr);
    }

    private function get_create_task_button() : string 
    {
        $btn = $this->get_neccessary_form_params();

        $attr = array(
            'type' => 'hidden', 
            'name' => \ViewMain::GUI_EVENT, 
            'value' => \ViewMain::USER_WORK 
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden', 
            'name' => \TaskAssignmentMain::ASSIGN_PAGE, 
            'value' => \TaskAssignmentMain::NEW_TASK 
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $text = get_string('create_new_task', 'coursework');
        $btn.= \html_writer::tag('button', $text);

        $attr = array(
            'method' => 'post',
            'style' => 'display: inline-block'
        );
        return \html_writer::tag('form', $btn, $attr);
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