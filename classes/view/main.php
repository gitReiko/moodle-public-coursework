<?php

require_once 'students_works_list/page.php';
require_once 'students_works/main.php';
require_once 'student_work/main.php';
require_once 'database_handlers/main.php';
require_once 'view_lib.php';

use Coursework\View\StudentsWorksList as swl;
use coursework_lib as lib;

class ViewMain 
{
    const DATABASE_EVENT = 'database_event';
    const GUI_EVENT = 'gui_event';
    const USER_WORK = 'user_work';

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
            global $USER;
            return $this->get_student_work_page($USER->id);
        }
        else 
        {
            $event = $this->get_gui_event();

            if($event == self::USER_WORK)
            {
                $studentId = $this->get_student_id();
                return $this->get_student_work_page($studentId);
            }
            else 
            {
                return $this->get_students_works_list_page();
            }
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

    private function get_students_works_list_page() : string 
    {
        $worksList = new swl\Page($this->course, $this->cm);
        return $worksList->get_page();
    }

    private function get_student_work_page(int $userId) : string 
    {
        $studentWork = new StudentWorkMain($this->course, $this->cm, $userId);
        return $studentWork->get_page();
    }

    private function get_gui_event()
    {
        return optional_param(self::GUI_EVENT, null, PARAM_TEXT);
    }

    private function get_student_id() : int 
    {
        return optional_param(STUDENT.ID, null, PARAM_INT);
    }


}

