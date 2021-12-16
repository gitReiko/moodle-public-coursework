<?php

namespace Coursework\View;

require_once 'students_works_list/page.php';
require_once 'student_work/main.php';
require_once 'database_handlers/main.php';

use Coursework\View\DatabaseHandlers\Main as MainDatabaseHandler;
use Coursework\View\StudentWork\Main as StudentWork;
use Coursework\View\StudentsWorksList as swl;
use Coursework\Lib\CommonLib as cl;

class Main 
{
    const DATABASE_EVENT = 'database_event';
    const GUI_EVENT = 'gui_event';
    const USER_WORK = 'user_work';

    const ID = 'id';
    const STUDENT_ID = 'studentid';

    private $course;
    private $cm;

    function __construct(\stdClass $course, \stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;
    }

    public function handle_database_event()
    {
        if($this->is_database_event_exist())
        {
            $this->handle_database_event_();
            $this->redirect_to_prevent_page_update();
        }
    }

    public function get_gui() : string 
    {
        global $USER;

        if(cl::is_user_student($this->cm, $USER->id))
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

    private function redirect_to_prevent_page_update()
    {
        global $USER;

        if(cl::is_user_student($this->cm, $USER->id))
        {
            $this->redirect_student_to_student_work();
        }
        else 
        {
            $event = $this->get_gui_event();

            if($event == self::USER_WORK)
            {
                $studentId = $this->get_student_id();
                return $this->redirect_user_to_student_work($studentId);
            }
            else 
            {
                return $this->redirect_user_to_student_works_list();
            }
        }
    }

    private function redirect_student_to_student_work()
    {
        $path = '/mod/coursework/view.php';
        $params = array('id'=>$this->cm->id);
        redirect(new \moodle_url($path, $params));
    }

    private function redirect_user_to_student_work(int $studentId)
    {
        $path = '/mod/coursework/view.php';
        $params = array(
            'id'=>$this->cm->id,
            self::GUI_EVENT => self::USER_WORK,
            self::STUDENT_ID => $studentId
        );
        redirect(new \moodle_url($path, $params));
    }

    private function redirect_user_to_student_works_list()
    {
        $path = '/mod/coursework/view.php';
        $params = array('id'=>$this->cm->id);
        redirect(new \moodle_url($path, $params));
    }

    private function is_database_event_exist() : bool 
    {
        $event = optional_param(DB_EVENT, null, PARAM_TEXT);

        if($event) return true;
        else return false;
    }

    private function handle_database_event_() : void 
    {
        $database = new MainDatabaseHandler($this->course, $this->cm);
        $database->handle();
    }

    private function get_students_works_list_page() : string 
    {
        $worksList = new swl\Page($this->course, $this->cm);
        return $worksList->get_page();
    }

    private function get_student_work_page(int $userId) : string 
    {
        $studentWork = new StudentWork($this->course, $this->cm, $userId);
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

