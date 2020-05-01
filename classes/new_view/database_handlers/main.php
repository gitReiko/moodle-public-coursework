<?php

require_once 'select_theme.php';
require_once 'use_task_template.php';
require_once 'custom_task_assignment.php';
require_once 'chat_message.php';

class ViewDatabaseHandler 
{
    const SELECT_THEME = 'select_theme';
    const USE_TASK_TEMPLATE = 'use_task_template';
    const CUSTOM_TASK_ASSIGNMENT = 'custom_task_assignment';
    const CHAT_MESSAGE = 'chat_message';
        
    private $course;
    private $cm;

    function __construct(stdClass $course, stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;
    }

    public function handle() : void 
    {
        $event = optional_param(DB_EVENT, null, PARAM_TEXT);

        switch($event)
        {
            case self::CHAT_MESSAGE : 
                $this->handle_chat_message_database_event();
                break;
            case self::SELECT_THEME : 
                $this->handle_select_theme_database_event();
                break;
            case self::USE_TASK_TEMPLATE : 
                $this->handle_use_task_template_database_event();
                break;
            case self::CUSTOM_TASK_ASSIGNMENT : 
                $this->handle_custom_task_assignment_database_event();
                break;
        }
    }

    private function handle_select_theme_database_event() : void 
    {
        $database = new ThemeSelectDatabaseHandler($this->course, $this->cm);
        $database->handle();
    }

    private function handle_use_task_template_database_event() : void 
    {
        $database = new UseTaskTemplateDatabaseHandler($this->course, $this->cm);
        $database->handle();
    }

    private function handle_custom_task_assignment_database_event() : void 
    {
        $database = new CustomTaskTemplateDatabaseHandler($this->course, $this->cm);
        $database->handle();
    }

    private function handle_chat_message_database_event() : void 
    {
        $database = new ChatMessageDatabaseHandler($this->course, $this->cm);
        $database->handle();
    }



}
