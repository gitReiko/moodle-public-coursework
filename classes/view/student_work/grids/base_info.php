<?php

namespace Coursework\View\StudentsWork\Modules;

use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\Getters\StudentsGetter as sg;

class BaseInfo 
{

    protected $course;
    protected $cm;
    protected $studentId;

    private $work;

    function __construct(\stdClass $course, \stdClass $cm, int $studentId)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->studentId = $studentId;

        $this->work = sg::get_students_work($this->cm->instance, $this->studentId);
    }

    public function get_module() : string 
    {
        $m = $this->get_coursework_name();
        $m.= $this->get_student_theme();
        $m.= $this->get_work_state();
        $m.= $this->get_work_grade();
        $m.= $this->get_work_student();
        $m.= $this->get_work_teacher();

        return $m;
    }

    private function get_coursework_name() : string 
    {
        $text = cg::get_coursework_name($this->cm->instance);
        return \html_writer::tag('h2', $text);
    }

    private function get_student_theme() : string 
    {
        $text = get_string('theme', 'coursework').': ';
        $text.= sg::get_student_theme($this->work);
        return \html_writer::tag('h3', $text);
    }

    private function get_work_state() : string 
    {
        $text = get_string('state', 'coursework').': ';
        $text.= cg::get_state_name($this->work->status);
        return \html_writer::tag('p', $text);
    }

    private function get_work_grade() : string 
    {
        $text = get_string('grade', 'coursework').': ';
        $text.= $this->work->grade;
        return \html_writer::tag('p', $text);
    }

    private function get_work_student() : string 
    {
        $text = get_string('student', 'coursework').': ';
        $text.= cg::get_user_name($this->work->student);
        return \html_writer::tag('p', $text);
    }

    private function get_work_teacher() : string 
    {
        $text = get_string('teacher', 'coursework').': ';
        $text.= cg::get_user_name($this->work->teacher);
        return \html_writer::tag('p', $text);
    }


}
