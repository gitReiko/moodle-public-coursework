<?php

require_once 'students_works/main.php';
require_once 'student_work/main.php';
require_once 'database_handlers/main.php';

use coursework_lib as lib;

class ViewMain 
{
    const DATABASE_EVENT = 'database_event';

    private $course;
    private $cm;

    function __construct(stdClass $course, stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        if($this->is_database_event_exist())
        {
            $this->handle_database_event();
        }
    }

    public function get_gui() : string 
    {
        global $USER;

        if(lib\is_user_student($this->cm, $USER->id))
        {
            return $this->get_student_work_gui();
        }
        else 
        {
            return $this->get_students_works_list_gui();
        }
    }

    private function is_database_event_exist() : bool 
    {
        $event = optional_param(DB_EVENT, null, PARAM_TEXT);

        if($event) return true;
        else return false;
    }

    private function handle_database_event() : void 
    {
        $database = new ViewDatabaseHandler($this->course, $this->cm);
        $database->handle();
    }

    private function get_students_works_list_gui() : string 
    {
        $worksList = new StudentsWorksMain($this->course, $this->cm);
        return $worksList->get_page();
    }

    private function get_student_work_gui() : string 
    {
        global $USER;
        $worksList = new StudentWorkMain($this->course, $this->cm, $USER->id);
        return $worksList->get_gui();
    }


}

