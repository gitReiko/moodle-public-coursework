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

    // Constructor functions
    function __construct($course, $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        if($this->is_database_event_isset())
        {
            $this->execute_database_handler();
        }      

        $this->students = $this->get_students();
    }

    public function execute() : string 
    {
        $page = new Page($this->course, $this->cm);
        return $page->get_page();
    }

    private function is_database_event_isset() : bool 
    {
        $dbEvent = optional_param(Main::DB_EVENT, null, PARAM_TEXT);

        if(isset($dbEvent)) return true;
        else return false;
    }

    private function execute_database_handler()
    {
        $handler = new Database($this->course, $this->cm);
        $handler->execute();  
    }

    private function get_students() : array
    {
        $students = array();
        $distributedStudents = $this->get_distributed_students();
        $allowedGroups = groups_get_activity_allowed_groups($this->cm);

        foreach($distributedStudents as $dStudent)
        {
            foreach($allowedGroups as $aGroup)
            {
                if(groups_is_member($aGroup->id, $dStudent->student))
                {
                    $students[] = $dStudent;
                    break;
                }
            }
        }

        return $students;
    }

    private function get_distributed_students()
    {
        global $DB;
        $conditions = array('coursework'=>$this->cm->instance);
        return $DB->get_records('coursework_students', $conditions);
    }

}

