<?php

namespace Coursework\View\DatabaseHandlers;

require_once 'assign_default_task.php';
require_once 'chat_message.php';
require_once 'check_task_section.php';
require_once 'check_work.php';
require_once 'custom_task_assignment.php';
require_once 'mark_messages_as_readed.php';
require_once 'select_theme.php';
require_once 'send_section_for_check.php';
require_once 'send_work_for_check.php';

class Main 
{
    const ASSIGN_DEFAULT_TASK = 'assign_default_task';
    const CHAT_MESSAGE = 'chat_message';
    const CHECK_TASK_SECTION = 'check_task_section';
    const CHECK_WORK = 'check_work';
    const COURSE = 'course';
    const CUSTOM_TASK_ASSIGNMENT = 'custom_task_assignment';
    const DB_EVENT = 'database_event';
    const DESCRIPTION = 'description';
    const GRADE = 'grade';
    const ID = 'id';
    const MESSAGE = 'message';
    const NAME = 'name';
    const OWN_THEME = 'own_theme';
    const SECTION = 'section';
    const SELECT_THEME = 'select_theme';
    const SEND_SECTION_FOR_CHECK = 'send_section_for_check';
    const SEND_WORK_FOR_CHECK = 'send_work_for_check';
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
            case self::CHAT_MESSAGE : 
                $this->handle_chat_message_database_event();
                break;
            case self::SELECT_THEME : 
                $this->handle_select_theme_database_event();
                break;
            case self::ASSIGN_DEFAULT_TASK : 
                $this->handle_assign_default_task_database_event();
                break;
            case self::CUSTOM_TASK_ASSIGNMENT : 
                $this->handle_custom_task_assignment_database_event();
                break;
            case self::SEND_SECTION_FOR_CHECK : 
                $this->handle_send_section_to_check_database_event();
                break;
            case self::SEND_WORK_FOR_CHECK : 
                $this->handle_send_work_to_check_database_event();
                break;
            case self::CHECK_TASK_SECTION : 
                $this->handle_check_task_section_database_event();
                break;
            case self::CHECK_WORK : 
                $this->handle_check_work_database_event();
                break;
        }
    }

    private function handle_select_theme_database_event() : void 
    {
        $database = new ThemeSelect($this->course, $this->cm);
        $database->handle();
    }

    private function handle_assign_default_task_database_event() : void 
    {
        $database = new AssignDefaultTask($this->course, $this->cm);
        $database->handle();
    }

    private function handle_custom_task_assignment_database_event() : void 
    {
        $database = new CustomTaskAssignment($this->course, $this->cm);
        $database->handle();
    }

    private function handle_chat_message_database_event() : void 
    {
        $database = new ChatMessage($this->course, $this->cm);
        $database->handle();
    }

    private function handle_send_section_to_check_database_event() : void 
    {
        $database = new SendSectionForCheck($this->course, $this->cm);
        $database->handle();
    }

    private function handle_send_work_to_check_database_event() : void 
    {
        $database = new SendWorkForCheck($this->course, $this->cm);
        $database->handle();
    }

    private function handle_check_task_section_database_event() : void 
    {
        $database = new CheckTaskSection($this->course, $this->cm);
        $database->handle();
    }

    private function handle_check_work_database_event() : void 
    {
        $database = new CheckWork($this->course, $this->cm);
        $database->handle();
    }


}
