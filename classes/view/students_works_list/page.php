<?php

namespace View\StudentsWorksList;

use CourseWork\LocalLib as lib;

require_once 'components/groups_selector.php';
require_once 'getters/main_getter.php';

class Page 
{
    const FORM_ID = 'swl_dashboard_form';
    const SELECTED_GROUP = 'selected_group';

    private $d;

    function __construct(\stdClass $course, \stdClass $cm) 
    {
        $this->d = new MainGetter($course, $cm);


        print_r($this->d->get_students());
    }

    public function get_page() : string 
    {
        $page = $this->get_form_start();
        $page.= $this->get_page_header();
        $page.= $this->get_group_selector();


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





    private function get_form_end() : string 
    {
        return \html_writer::end_tag('form');
    }

}