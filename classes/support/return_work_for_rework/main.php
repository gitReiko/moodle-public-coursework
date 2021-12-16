<?php

namespace Coursework\Support\BackToWorkState;

require_once 'database.php';
require_once 'page.php';

class Main 
{
    const RETURN_WORK_FOR_REWORK = 'return_work_for_rework';
    const STUDENT_ID = 'student_id';
    const COURSEWORK_ID = 'coursework_id';
 
    private $cm;
    private $course;

    function __construct(\stdClass $cm, \stdClass $course) 
    {
        $this->cm = $cm;
        $this->course = $course;

        $this->log_user_view_return_work_for_rework_page();
    }

    public function handle_database_event()
    {
        if($this->is_database_event_exists())
        {
            $this->execute_database_handler();
            $this->redirect_to_prevent_page_update();
        }
    }

    public function get_page() : string  
    {
        return $this->get_page_();
    }

    private function redirect_to_prevent_page_update() : void
    {
        $path = '/mod/coursework/pages/support/return_work_for_rework.php';
        $params = array('id'=>$this->cm->id);
        redirect(new \moodle_url($path, $params));
    }

    private function log_user_view_return_work_for_rework_page()
    {
        $params = array
        (
            'context' => \context_module::instance($this->cm->id)
        );

        $event = \mod_coursework\event\user_view_return_work_for_rework_page::create($params);
        $event->trigger();
    }

    private function is_database_event_exists() : bool
    {
        $back = optional_param(self::RETURN_WORK_FOR_REWORK, null, PARAM_TEXT);

        if($back) return true;
        else return false;
    }

    private function get_page_() : string 
    {
        $p = new Page($this->cm);
        return $p->get_page();
    }

    private function execute_database_handler() : void 
    {
        $database = new Database($this->cm, $this->course);
        $database->change_state_to_work();
    }


}

