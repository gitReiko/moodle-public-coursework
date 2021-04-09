<?php

namespace Coursework\View\StudentsWorksList;

require_once 'components/groups_selector.php';
require_once 'components/teachers_selector.php';
require_once 'components/courses_selector.php';
require_once 'components/students_table.php';
require_once 'components/students_table/main.php';
require_once 'getters/main_getter.php';

use Coursework\View\StudentsWorksList\StudentsTable as st;

class Page 
{
    const FORM_ID = 'swl_dashboard_form';
    
    private $d;

    function __construct(\stdClass $course, \stdClass $cm) 
    {
        $this->d = new MainGetter($course, $cm);
    }

    public function get_page() : string 
    {
        $page = $this->get_form_start();
        $page.= $this->get_page_header();
        $page.= $this->get_group_selector();
        $page.= $this->get_teachers_selector();
        $page.= $this->get_courses_selector();
        $page.= $this->get_students_table();
        $page.= $this->get_form_end();

        return $page;
    }

    private function get_form_start() : string  
    {
        $attr = array('id' => self::FORM_ID, 'method' => 'post');
        return \html_writer::start_tag('form', $attr);
    }

    private function get_page_header() : string 
    {
        $text = $this->d->get_course_work_name();
        return \html_writer::tag('h2', $text);
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
        $tbl = new StudentsTable($this->d);
        $old = $tbl->get_students_table();

        $main = New st\Main($this->d);
        $new = $main->get_students_table();

        return $old.'<hr>'.$new;
    }

    private function get_form_end() : string 
    {
        return \html_writer::end_tag('form');
    }

}