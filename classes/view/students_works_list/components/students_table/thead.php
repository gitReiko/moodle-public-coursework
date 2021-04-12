<?php 

namespace Coursework\View\StudentsWorksList\StudentsTable;

use Coursework\View\StudentsWorksList as swl;

class Thead 
{

    public function get() : string 
    {
        $head = \html_writer::start_tag('thead');
        $head.= \html_writer::start_tag('tr');

        $head.= $this->get_notifications_cell();
        $head.= $this->get_more_details_cell();
        $head.= $this->get_go_to_work_cell();
        $head.= $this->get_student_cell();
        $head.= $this->get_state_cell();
        $head.= $this->get_theme_cell();
        $head.= $this->get_grade_cell();

        $head.= \html_writer::end_tag('tr');
        $head.= \html_writer::end_tag('thead');

        return $head;
    }

    private function get_notifications_cell() : string 
    {
        $attr = array('title' => get_string('notifications', 'coursework'));
        $text = '<i class="fa fa-exclamation-triangle"></i>';
        return \html_writer::tag('td', $text, $attr); 
    }

    private function get_more_details_cell() : string 
    {
        $attr = array('title' => get_string('more_details', 'coursework'));
        $text = '<i class="fa fa-arrow-down"></i>';
        return \html_writer::tag('td', $text, $attr);
    }

    private function get_go_to_work_cell() : string 
    {
        $attr = array('title' => get_string('go_to_student_work', 'coursework'));
        $text = get_string('work', 'coursework');
        return \html_writer::tag('td', $text, $attr);
    }

    private function get_student_cell() : string 
    {
        $text = get_string('student', 'coursework');
        return \html_writer::tag('td', $text);
    }

    private function get_state_cell() : string 
    {
        $text = get_string('state', 'coursework');
        return \html_writer::tag('td', $text);
    }

    private function get_theme_cell() : string 
    {
        $text = get_string('theme', 'coursework');
        return \html_writer::tag('td', $text);
    }

    private function get_grade_cell() : string 
    {
        $text = get_string('grade_short', 'coursework');
        return \html_writer::tag('td', $text);
    }



}
