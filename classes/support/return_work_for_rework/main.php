<?php

namespace Coursework\Support\BackToWorkState;

require_once '../../classes/lib/main_template.php';
require_once 'database.php';
require_once 'locallib.php';
require_once 'page.php';

class Main extends \Coursework\Classes\Lib\MainTemplate
{
    const MODULE_URL = '/mod/coursework/pages/support/return_work_for_rework.php';

    const RETURN_WORK_FOR_REWORK = 'return_work_for_rework';
    const STUDENT_ID = 'student_id';
    const COURSEWORK_ID = 'coursework_id';
 
    protected $cm;
    protected $course;

    function __construct(\stdClass $cm, \stdClass $course) 
    {
        $this->cm = $cm;
        $this->course = $course;

        parent::__construct($course, $cm);

        $this->log_user_view_return_work_for_rework_page();
    }

    protected function get_redirect_path() : string
    {
        return '/mod/coursework/pages/support/return_work_for_rework.php';
    }

    protected function get_redirect_params() : array
    {
        return array(
            'id' => $this->cm->id,
            self::STUDENT_ID => LocalLib::get_student_id()
        );
    }

    protected function get_content() : string 
    {
        $p = new Page($this->cm);
        return $p->get_page();
    }

    protected function execute_database_handler() 
    {
        $database = new Database($this->cm, $this->course);
        return $database->change_state_to_work();
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

}
