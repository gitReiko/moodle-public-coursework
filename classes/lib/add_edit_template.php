<?php

namespace Coursework\Classes\Lib;

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

    public function get_page() : string 
    {
        return $this->get_gui();
    }

    public function handle_database_event()
    {
        if($this->is_database_event_exist())
        {
            $this->execute_database_handler();
            $this->redirect_to_prevent_page_update();
        }
    }

    abstract protected function execute_database_handler() : void;

    abstract protected function redirect_to_prevent_page_update() : void;

    protected function is_database_event_exist() : bool 
    {
        $event = optional_param(self::DATABASE_EVENT, null, PARAM_TEXT);

        if(isset($event)) return true;
        else return false;
    }

    abstract protected function get_gui() : string;

}
