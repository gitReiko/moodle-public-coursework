<?php

namespace Coursework\View\StudentsWorksList;

require_once 'components/groups_selector.php';
require_once 'components/teachers_selector.php';
require_once 'components/courses_selector.php';
require_once 'components/students_table/main.php';
require_once 'getters/main_getter.php';

use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\View\StudentsWorksList\StudentsTable as st;

class Page 
{
    const FORM_ID = 'swl_dashboard_form';
    
    private $cm;
    private $d;

    function __construct(\stdClass $course, \stdClass $cm) 
    {
        $this->cm = $cm;
        $this->d = new MainGetter($course, $cm);
        $this->log_event_user_view_students_works();
    }

    public function get_page() : string 
    {
        if($this->is_teachers_exists())
        {
            $page = $this->get_form_start();
            $page.= cg::get_page_header($this->d->get_cm());
            $page.= $this->get_group_selector();
            $page.= $this->get_teachers_selector();
            $page.= $this->get_courses_selector();
            $page.= $this->get_students_table();
            $page.= $this->get_form_end();
        }
        else 
        {
            $page = $this->get_message_teachers_not_configured();
        }

        return $page;
    }

    private function log_event_user_view_students_works()
    {
        $params = array
        (
            'context' => \context_module::instance($this->cm->id)
        );

        $event = \mod_coursework\event\user_view_students_works_list::create($params);
        $event->trigger();
    }

    private function is_teachers_exists() : bool 
    {
        if(is_array($this->d->get_teachers()))
        {
            if(count($this->d->get_teachers()) > 0)
            {
                return true;
            }
            else 
            {
                return false;
            }
        }
        else 
        {
            return false;
        }
    }

    private function get_message_teachers_not_configured() : string 
    {
        $attr = array('class' => 'no_students_message');
        $text = get_string('teachers_not_configured', 'coursework');
        $msg = \html_writer::tag('p', $text, $attr);

        $url = $this->get_leader_setting_url();
        $attr = array('href' => $url);
        $text = get_string('go_to_configuration_page', 'coursework');
        $a = \html_writer::tag('a', $text, $attr);
        $attr = array('class' => 'large_text');
        $msg.= \html_writer::tag('p', $a, $attr);

        return $msg;
    }

    private function get_leader_setting_url() : string 
    {
        $url = '/mod/coursework/configuration.php';
        $url.= '?id='.$this->d->get_cm()->id;
        $url.= '&'.CONFIG_MODULE.'='.LEADERS_SETTING;

        return $url;
    }

    private function get_form_start() : string  
    {
        $attr = array('id' => self::FORM_ID, 'method' => 'post');
        return \html_writer::start_tag('form', $attr);
    }

    private function get_group_selector() : string 
    {
        $selector = new GroupsSelector($this->d);
        return $selector->get_groups_selector();
    }

    private function get_teachers_selector() : string 
    {        
        $selector = new TeachersSelector($this->d);
        return $selector->get_teachers_selector();
    }

    private function get_courses_selector() : string 
    {        
        $selector = new CoursesSelector($this->d);
        return $selector->get_courses_selector();
    }

    private function get_students_table() : string 
    {
        $main = New st\Main($this->d);
        return $main->get_students_table();
    }

    private function get_form_end() : string 
    {
        return \html_writer::end_tag('form');
    }

}