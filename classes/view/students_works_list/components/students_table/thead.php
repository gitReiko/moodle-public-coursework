<?php 

namespace Coursework\View\StudentsWorksList\StudentsTable;

use Coursework\View\StudentsWorksList as swl;

class Thead 
{

    public function get() : string 
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
