<?php

namespace Coursework\View\DatabaseHandlers;

require_once 'add_chat_message.php';
require_once 'assign_custom_task.php';
require_once 'assign_default_task.php';
require_once 'check_task_section.php';
require_once 'check_work.php';
require_once 'mark_messages_as_readed.php';
require_once 'select_theme.php';
require_once 'send_section_for_check.php';
require_once 'send_work_for_check.php';

class Main 
{
    // Database handlers
    const ADD_CHAT_MESSAGE = 'add_chat_message';
    const ASSIGN_CUSTOM_TASK = 'assign_custom_task';
    const ASSIGN_DEFAULT_TASK = 'assign_default_task';
    const CHECK_TASK_SECTION = 'check_task_section';
    const CHECK_WORK = 'check_work';
    const SELECT_THEME = 'select_theme';
    const SEND_SECTION_FOR_CHECK = 'send_section_for_check';
    const SEND_WORK_FOR_CHECK = 'send_work_for_check';
    
    // Field types
    const COURSE = 'course';
    const DB_EVENT = 'database_event';
    const DESCRIPTION = 'description';
    const GRADE = 'grade';
    const ID = 'id';
    const MESSAGE = 'message';
    const NAME = 'name';
    const OWN_THEME = 'own_theme';
    const SECTION = 'section';
    const STATUS = 'status';
    const STUDENT = 'student';
    const STUDENT_ID = 'student_id';
    const TEACHER = 'teacher';
    const THEME = 'theme';
    const USERFROM = 'userfrom';
    const USERTO = 'userto';
        
    private $course;
    private $cm;

    function __construct(\stdClass $course, \stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;
    }

    public function handle() : void 
    {
        $event = optional_param(self::DB_EVENT, null, PARAM_TEXT);

        switch($event)
        {
            case self::ADD_CHAT_MESSAGE : 
                $this->handle_event_add_chat_message();
                break;
            case self::ASSIGN_CUSTOM_TASK : 
                $this->handle_event_assign_custom_task();
                break;
            case self::ASSIGN_DEFAULT_TASK : 
                $this->handle_event_assign_default_task();
                break;
            case self::CHECK_TASK_SECTION : 
                $this->handle_event_check_task_section();
                break;
            case self::CHECK_WORK : 
                $this->handle_event_check_work();
                break;
            case self::SELECT_THEME : 
                $this->handle_event_select_theme();
                break;
            case self::SEND_SECTION_FOR_CHECK : 
                $this->handle_event_send_section_for_check();
                break;
            case self::SEND_WORK_FOR_CHECK : 
                $this->handle_event_send_work_for_check();
                break;
        }
    }

    private function handle_event_add_chat_message() : void 
    {
        $database = new AddChatMessage($this->course, $this->cm);
        $database->handle();
    }

    private function handle_event_assign_custom_task() : void 
    {
        $database = new AssignCustomTask($this->course, $this->cm);
        $database->handle();
    }

    private function handle_event_assign_default_task() : void 
    {
        $database = new AssignDefaultTask($this->course, $this->cm);
        $database->handle();
    }

    private function handle_event_check_task_section() : void 
    {
        $database = new CheckTaskSection($this->course, $this->cm);
        $database->handle();
    }

    private function handle_event_check_work() : void 
    {
        $database = new CheckWork($this->course, $this->cm);
        $database->handle();
    }

    private function handle_event_select_theme() : void 
    {
        $database = new ThemeSelect($this->course, $this->cm);
        $database->handle();
    }

    private function handle_event_send_section_for_check() : void 
    {
        $database = new SendSectionForCheck($this->course, $this->cm);
        $database->handle();
    }

    private function handle_event_send_work_for_check() : void 
    {
        $database = new SendWorkForCheck($this->course, $this->cm);
        $database->handle();
    }

}
