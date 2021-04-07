<?php

namespace Coursework\View\StudentsWorksList;

use Coursework\Lib\Enums as enum;
use Coursework\View\StudentsWorksList\Page as p;

class StudentsTable 
{

    private $d;

    function __construct(MainGetter $d) 
    {
        $this->d = $d;
    }

    public function get_students_table() : string 
    {
        $attr = array('class' => 'studentsWorksList');
        $tbl = \html_writer::start_tag('table', $attr);
        $tbl.= $this->get_table_header();

        $tbl.= \html_writer::end_tag('table');

        return $tbl;
    }

    private function get_table_header() : string 
    {
        $head = \html_writer::start_tag('thead');
        $head.= \html_writer::start_tag('tr');

        $attr = array('title' => get_string('notifications', 'coursework'));
        $text = '<i class="fa fa-exclamation-triangle"></i>';
        $head.= \html_writer::tag('td', $text, $attr);

        $attr = array('title' => get_string('more_details', 'coursework'));
        $text = '<i class="fa fa-arrow-down"></i>';
        $head.= \html_writer::tag('td', $text, $attr);

        $attr = array('title' => get_string('go_to_student_work', 'coursework'));
        $text = get_string('work', 'coursework');
        $head.= \html_writer::tag('td', $text, $attr);

        $text = get_string('student', 'coursework');
        $head.= \html_writer::tag('td', $text);

        $text = get_string('state', 'coursework');
        $head.= \html_writer::tag('td', $text);

        $text = get_string('theme', 'coursework');
        $head.= \html_writer::tag('td', $text);

        $text = get_string('grade_short', 'coursework');
        $head.= \html_writer::tag('td', $text);

        $head.= \html_writer::end_tag('tr');
        $head.= \html_writer::end_tag('thead');

        return $head;
    }



}