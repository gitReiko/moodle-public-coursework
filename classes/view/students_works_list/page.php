<?php

namespace View\StudentsWorksList;

use CourseWork\LocalLib as lib;

require_once 'components/groups_selector.php';
require_once 'getter.php';

class Page 
{
    private $d;

    function __construct(\stdClass $course, \stdClass $cm) 
    {
        $this->d = new Getter($course, $cm);

    }

    public function get_page() : string 
    {
        $page = $this->get_page_header();
        $page.= $this->get_group_selector();



        return $page;
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



}