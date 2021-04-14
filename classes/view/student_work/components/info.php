<?php

namespace Coursework\View\StudentsWork\Components;

use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\Getters\StudentsGetter as sg;

class Info extends Base
{
    private $work;

    function __construct(\stdClass $course, \stdClass $cm, int $studentId)
    {
        parent::__construct($course, $cm, $studentId);

        $this->work = sg::get_students_work($this->cm->instance, $this->studentId);
    }

    protected function get_hiding_class_name() : string
    {
        return 'work_info_content';
    }

    protected function get_header_text() : string
    {
        return get_string('work_info', 'coursework');
    }

    protected function get_content() : string
    {
        $attr = array('class' => 'workInfoGrids');
        $content = \html_writer::start_tag('div', $attr);
        $content.= $this->get_theme_cell();
        $content.= $this->get_course_cell();
        $content.= $this->get_student_cell();
        $content.= $this->get_teacher_cell();
        $content.= $this->get_state_cell();
        $content.= $this->get_grade_cell();
        $content.= \html_writer::end_tag('div');

        return $content;
    }

    private function get_theme_cell() : string 
    {
        $attr = array('class' => $this->hidingСlassName);
        $text = get_string('theme', 'coursework').': ';
        $text.= sg::get_student_theme($this->work);
        return \html_writer::tag('div', $text, $attr);
    }

    private function get_course_cell() : string 
    {
        $attr = array('class' => $this->hidingСlassName);
        $text = get_string('course', 'coursework').': ';
        $text.= cg::get_course_name($this->work->course);
        return \html_writer::tag('div', $text, $attr);
    }

    private function get_student_cell() : string 
    {
        $attr = array('class' => $this->hidingСlassName);
        $text = get_string('student', 'coursework').': ';
        $text.= cg::get_user_photo($this->work->student);
        $text.= cg::get_user_name($this->work->student);
        return \html_writer::tag('div', $text, $attr);
    }

    private function get_teacher_cell() : string 
    {
        $attr = array('class' => $this->hidingСlassName);
        $text = get_string('teacher', 'coursework').': ';
        $text.= cg::get_user_photo($this->work->teacher);
        $text.= cg::get_user_name($this->work->teacher);
        return \html_writer::tag('div', $text, $attr);
    }

    private function get_state_cell() : string 
    {
        $attr = array('class' => $this->hidingСlassName);
        $text = get_string('state', 'coursework').': ';
        $text.= cg::get_state_name($this->work->status);
        return \html_writer::tag('div', $text, $attr);
    }

    private function get_grade_cell() : string 
    {
        $attr = array('class' => $this->hidingСlassName);
        $text = get_string('grade', 'coursework').': ';
        $text.= $this->work->grade;
        return \html_writer::tag('div', $text, $attr);
    }


}
