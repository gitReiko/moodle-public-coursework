<?php

namespace Coursework\View\StudentWork\Components;

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

        $content.= $this->get_student_grid();
        $content.= $this->get_teacher_grid();

        $content.= \html_writer::end_tag('div');

        return $content;
    }

    private function get_student_grid() : string 
    {
        $inner = $this->get_student_photo();
        $td = \html_writer::tag('td', $inner);

        $inner = $this->get_theme();
        $inner.= $this->get_course();
        $inner.= $this->get_student_name();
        $td.= \html_writer::tag('td', $inner);

        $tr = \html_writer::tag('tr', $td);

        $attr = array('class' => 'studentGrid');
        $table = \html_writer::tag('table', $tr, $attr);

        $grid = \html_writer::tag('div', $table);

        return $grid;
    }

    private function get_student_photo() : string 
    {
        $inner = cg::get_big_user_photo($this->work->student);
        return \html_writer::tag('p', $inner);
    }

    private function get_theme() : string 
    {
        $inner = get_string('theme', 'coursework').': ';
        $inner = \html_writer::tag('b', $inner);
        $inner.= sg::get_student_theme($this->work);
        return \html_writer::tag('p', $inner);
    }

    private function get_course() : string 
    {
        $inner = get_string('course', 'coursework').': ';
        $inner = \html_writer::tag('b', $inner);
        $inner.= cg::get_course_name($this->work->course);
        return \html_writer::tag('p', $inner);
    }

    private function get_student_name() : string 
    {
        $inner = get_string('student', 'coursework').': ';
        $inner = \html_writer::tag('b', $inner);
        $inner.= cg::get_user_name($this->work->student);
        return \html_writer::tag('p', $inner);
    }

    private function get_teacher_grid() : string 
    {
        $inner = $this->get_work_state();
        $inner.= $this->get_work_grade();
        $inner.= $this->get_teacher_name();
        $td = \html_writer::tag('td', $inner);

        $inner = $this->get_teacher_photo();
        $td.= \html_writer::tag('td', $inner);

        $tr = \html_writer::tag('tr', $td);

        $attr = array('class' => 'teacherGrid');
        $table = \html_writer::tag('table', $tr, $attr);

        $grid = \html_writer::tag('div', $table);

        return $grid;
    }

    private function get_teacher_photo() : string 
    {
        $inner = cg::get_big_user_photo($this->work->teacher);
        return \html_writer::tag('div', $inner);
    }

    private function get_work_state() : string 
    {
        $inner = get_string('state', 'coursework').': ';
        $inner = \html_writer::tag('b', $inner);
        $inner.= cg::get_state_name($this->work->status);
        return \html_writer::tag('p', $inner);
    }

    private function get_work_grade() : string 
    {
        $inner = get_string('grade', 'coursework').': ';
        $inner = \html_writer::tag('b', $inner);
        $inner.= $this->work->grade;
        return \html_writer::tag('p', $inner);
    }

    private function get_teacher_name() : string 
    {
        $inner = get_string('teacher', 'coursework').': ';
        $inner = \html_writer::tag('b', $inner);
        $inner.= cg::get_user_name($this->work->teacher);
        return \html_writer::tag('p', $inner);
    }



}
