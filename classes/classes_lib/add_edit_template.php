<?php

namespace Coursework\ClassesLib;

abstract class AddEditTemplate
{
    const DATABASE_EVENT = 'database_event';
    const GUI_TYPE = 'gui_type';
    
    const ID = 'id';

    protected $course;
    protected $cm;

    function __construct(\stdClass $course, \stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;
    }

    public function execute() : string 
    {
        $this->handle_database_event();
        return $this->get_gui();
    }

    abstract protected function handle_database_event() : void;

    protected function is_database_event_exist() : bool 
    {
        $event = optional_param(self::DATABASE_EVENT, null, PARAM_TEXT);

        if(isset($event)) return true;
        else return false;
    }

    abstract protected function get_gui() : string;

}
