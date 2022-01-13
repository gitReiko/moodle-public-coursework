<?php

namespace Coursework\View;

require_once 'students_works_list/page.php';
require_once 'student_work/main.php';
require_once 'database_handlers/main.php';

use Coursework\View\DatabaseHandlers\Main as MainDB;
use Coursework\View\StudentWork\Main as StudentWork;
use Coursework\Lib\Getters\CommonGetter as cg;
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
            $this->execute_database_handler();
            $this->redirect_to_prevent_page_update();
        }
    }

    public function get_gui() : string 
    {
        $page = cg::get_page_header($this->cm);
        
        if($this->is_coursework_leaders_exists())
        {
            global $USER;

            if(cl::is_user_student($this->cm, $USER->id))
            {
                $page.= $this->get_student_work_page($USER->id);
            }
            else 
            {
                $event = $this->get_gui_event();
    
                if($event == self::USER_WORK)
                {
                    $studentId = $this->get_student_id();
                    $page.= $this->get_student_work_page($studentId);
                }
                else 
                {
                    $page.= $this->get_students_works_list_page();
                }
            }
        }
        else 
        {
            $page.= $this->get_unconfigured_leaders_notify();
        }

        return $page;
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
        $event = optional_param(MainDB::DB_EVENT, null, PARAM_TEXT);

        if($event) return true;
        else return false;
    }

    private function execute_database_handler() : void 
    {
        $database = new MainDB($this->course, $this->cm);
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
        return optional_param(MainDB::STUDENT_ID, null, PARAM_INT);
    }

    private function is_coursework_leaders_exists() : bool 
    {
        global $DB;
        $params = array('coursework' => $this->cm->instance);
        return $DB->record_exists('coursework_teachers', $params);
    }

    private function get_unconfigured_leaders_notify() : string 
    {
        $notify = '';

        $text = get_string('appointed_leaders_not_exists', 'coursework');
        $notify.= \html_writer::tag('p', $text);

        $context = \context_module::instance($this->cm->id);
        if(has_capability('mod/coursework:settingleaders', $context))
        {
            $href = '/mod/coursework/pages/config/appoint_leaders.php?id='.$this->cm->id;
            $text = get_string('go_to_leaders_appointment', 'coursework');
            $text = \html_writer::tag('p', $text);

            $notify.= \html_writer::tag('a', $text, array('href' => $href));
        }
        else 
        {
            $text = get_string('contact_the_teachers', 'coursework');
            $notify.= \html_writer::tag('p', $text);
        }

        $attr = array('class' => 'error-notify');
        $notify = \html_writer::tag('div', $notify, $attr);

        return $notify;
    }


}

