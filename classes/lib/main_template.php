<?php

namespace Coursework\Classes\Lib;

use Coursework\Lib\Feedbacker;
use Coursework\Lib\Enums;

abstract class MainTemplate
{
    const DATABASE_EVENT = 'database_event';
    const GUI_TYPE = 'gui_type';
    
    const ID = 'id';

    protected $course;
    protected $cm;
    protected $feedback;

    function __construct(\stdClass $course, \stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->handle_database_event();

        $this->feedback = Feedbacker::get_feedback_from_post();
    }

    public function get_page() : string 
    {
        $page = $this->feedback;
        $page.= $this->get_content();
        return $page;
    }

    private function handle_database_event()
    {
        if($this->is_database_event_exists())
        {
            $feedback = $this->execute_database_handler();
            $this->redirect_to_prevent_page_update($feedback);
        }
    }

    abstract protected function execute_database_handler();

    protected function redirect_to_prevent_page_update($feedback) : void
    {
        $path = $this->get_redirect_path();
        $params = $this->get_redirect_params();
        $params = array_merge($params, $this->get_feedback_params($feedback));

        redirect(new \moodle_url($path, $params));
    }

    abstract protected function get_redirect_path() : string;

    abstract protected function get_redirect_params() : array;

    protected function get_feedback_params($feedback) : array 
    {
        $params = array();

        if(!empty($feedback))
        {
            $params = array_merge($params, array(Enums::FEEDBACK => $feedback));
        }

        return $params;
    }

    protected function is_database_event_exists() : bool 
    {
        $event = optional_param(self::DATABASE_EVENT, null, PARAM_TEXT);

        if(isset($event)) return true;
        else return false;
    }

    abstract protected function get_content() : string;

}
