<?php

namespace Coursework\Support\BackToWorkState;

require_once 'database.php';
require_once 'page.php';

class Main 
{
    const BACK_TO_WORK_STATE = 'back_to_work_state';
    const STUDENT_ID = 'student_id';
    const COURSEWORK_ID = 'coursework_id';
 
    private $cm;
    private $course;

    function __construct(\stdClass $cm, \stdClass $course) 
    {
        $this->cm = $cm;
        $this->course = $course;

        $this->log_event_user_view_back_to_work_state_page();
    }

    public function get_page() : string  
    {
        if($this->is_neccessary_back_to_work_state())
        {
            $this->change_state_to_work();
        }

        return $this->get_page_();
    }

    private function log_event_user_view_back_to_work_state_page()
    {
        $params = array
        (
            'context' => \context_module::instance($this->cm->id)
        );

        $event = \mod_coursework\event\user_view_back_to_work_state_page::create($params);
        $event->trigger();
    }

    private function is_neccessary_back_to_work_state() : bool
    {
        $back = optional_param(self::BACK_TO_WORK_STATE, null, PARAM_TEXT);

        if($back) return true;
        else return false;
    }

    private function get_page_() : string 
    {
        $p = new Page($this->cm);
        return $p->get_page();
    }

    private function change_state_to_work() : void 
    {
        $database = new Database($this->cm, $this->course);
        $database->change_state_to_work();
    }


}

