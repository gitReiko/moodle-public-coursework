<?php

namespace Coursework\Support\DeleteStudentCoursework;

require_once 'page.php';
require_once 'database.php';

class Main 
{
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

        if($this->is_database_event_exists())
        {
            $this->execute_database_event();
        }
    }

    public function execute() : string 
    {
        $page = new Page($this->course, $this->cm);
        return $page->get_page();
    }

    private function is_database_event_exists() : bool 
    {
        $dbEvent = optional_param(Main::DB_EVENT, null, PARAM_TEXT);

        if(isset($dbEvent)) return true;
        else return false;
    }

    private function execute_database_event()
    {
        $handler = new Database($this->course, $this->cm);
        $handler->execute();  
    }

}

