<?php

namespace Coursework\View\StudentsWorksList;

use Coursework\View\StudentsWorksList\Page as p;
use Coursework\Lib\Enums as enum;
use ViewMain as m;

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
        $tbl.= $this->get_table_body();
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

    private function get_table_body() : string 
    {
        $body = \html_writer::start_tag('tbody');

        foreach($this->d->get_students() as $student)
        {
            $body.= \html_writer::start_tag('tr');

            $text = '';
            $body.= \html_writer::tag('td', $text);

            $text = '';
            $body.= \html_writer::tag('td', $text);

            $body.= $this->get_work_cell($student);
            $body.= $this->get_student_cell($student);
            $body.= $this->get_state_cell($student);
            $body.= $this->get_theme_cell($student);
            $body.= $this->get_grade_cell($student);

            $body.= \html_writer::end_tag('tr');
        }

        $body.= \html_writer::end_tag('tbody');

        return $body;
    }

    private function get_work_cell(\stdClass $student) : string 
    {
        $attr = array(
            'href' => $this->get_go_to_work_url($student),
            'target' => '_blank',
            'title' => get_string('go_to_student_work', 'coursework')
        );
        $text = get_string('work', 'coursework');
        $a = \html_writer::tag('a', $text, $attr);
        return \html_writer::tag('td', $a);
    }

    private function get_go_to_work_url(\stdClass $student)
    {
        $url = '/mod/coursework/view.php';
        $url.= '?'.m::ID.'='.$this->d->get_cm()->id;
        $url.= '&'.m::GUI_EVENT.'='.m::USER_WORK;
        $url.= '&'.m::STUDENT_ID.'='.$student->id;

        return $url;
    }

    private function get_student_cell(\stdClass $student) : string 
    {
        $text = $student->lastname.''.$student->firstname;
        return \html_writer::tag('td', $text);
    }

    private function get_state_cell(\stdClass $student) : string 
    {
        switch($student->status)
        {
            case enum::NOT_READY:
                $text = get_string('work_not_ready', 'coursework');
                break;
            case enum::READY:
                $text = get_string('work_ready', 'coursework');
                break;
            case enum::NEED_TO_FIX:
                $text = get_string('work_need_to_fix', 'coursework');
                break;
            case enum::SENT_TO_CHECK:
                $text = get_string('work_sent_to_check', 'coursework');
                break;
        }

        return \html_writer::tag('td', $text);
    }

    private function get_theme_cell(\stdClass $student) : string 
    {
        $text = $student->theme;
        return \html_writer::tag('td', $text);
    }

    private function get_grade_cell(\stdClass $student) : string 
    {
        $attr = array('class' => 'center');

        if(empty($student->grade))
        {
            $text = '';
        }
        else 
        {
            $text = $student->grade;
        }

        return \html_writer::tag('td', $text, $attr);
    }






}