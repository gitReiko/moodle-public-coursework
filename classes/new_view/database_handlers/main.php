<?php

require_once 'select_theme.php';

class ViewDatabaseHandler 
{
    const SELECT_THEME = 'select_theme';
        
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
            case self::SELECT_THEME : 
                $this->handle_select_theme_database_event();
                break;
        }
    }

    private function handle_select_theme_database_event() : void 
    {
        $database = new ThemeSelectDatabaseHandler($this->course, $this->cm);
        $database->handle();
    }



}
