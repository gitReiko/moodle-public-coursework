<?php

namespace Coursework\Support\DeleteStudentCoursework;

require_once 'page.php';
require_once 'database.php';

class Main 
{
    const ID = 'id';
    const DB_EVENT = 'db_event';
    const STUDENT_ID = 'student_id';

    private $course;
    private $cm;

    private $students;

    private $autofocus = true;

    function __construct($course, $cm)
    {
        $this->course = $course;
        $this->cm = $cm;
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
        $page = new Page($this->course, $this->cm);
        return $page->get_page();
    }

    private function execute_database_handler() : void
    {
        $handler = new Database($this->course, $this->cm);
        $handler->execute();
    }

    private function redirect_to_prevent_page_update() : void
    {
        $path = '/mod/coursework/pages/support/delete_student_coursework.php';
        $params = array('id'=>$this->cm->id);
        redirect(new \moodle_url($path, $params));
    }

    private function is_database_event_exists() : bool 
    {
        $dbEvent = optional_param(Main::DB_EVENT, null, PARAM_TEXT);

        if(isset($dbEvent)) return true;
        else return false;
    }

}

