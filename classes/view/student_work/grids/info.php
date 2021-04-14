<?php

namespace Coursework\View\StudentsWork\Grids;

use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\Getters\StudentsGetter as sg;

class Info 
{
    const HIDDEN_CLASS = 'work_info_content';

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

    public function get_grid() : string 
    {
        $attr = array('class' => 'workInfoGrids yellowGrid');
        $grids = \html_writer::start_tag('div', $attr);

        $grids.= $this->get_header_cell();
        $grids.= $this->get_theme_cell();
        $grids.= $this->get_course_cell();
        $grids.= $this->get_student_cell();
        $grids.= $this->get_teacher_cell();
        $grids.= $this->get_state_cell();
        $grids.= $this->get_grade_cell();

        $grids.= \html_writer::end_tag('div');

        return $grids;
    }

    private function get_header_cell() : string 
    {
        $attr = array(
            'class' => 'header',
            'onclick' => 'open_close_by_class(`'.self::HIDDEN_CLASS.'`)'
        );
        $text = get_string('work_info', 'coursework').' ';
        $text.= '('.get_string('click_to_show_hide', 'coursework').')';
        return \html_writer::tag('div', $text, $attr);
    }

    private function get_theme_cell() : string 
    {
        $attr = array('class' => self::HIDDEN_CLASS);
        $text = get_string('theme', 'coursework').': ';
        $text.= sg::get_student_theme($this->work);
        return \html_writer::tag('div', $text, $attr);
    }

    private function get_course_cell() : string 
    {
        $attr = array('class' => self::HIDDEN_CLASS);
        $text = get_string('course', 'coursework').': ';
        $text.= cg::get_course_name($this->work->course);
        return \html_writer::tag('div', $text, $attr);
    }

    private function get_student_cell() : string 
    {
        $attr = array('class' => self::HIDDEN_CLASS);
        $text = get_string('student', 'coursework').': ';
        $text.= cg::get_user_photo($this->work->student);
        $text.= cg::get_user_name($this->work->student);
        return \html_writer::tag('div', $text, $attr);
    }

    private function get_teacher_cell() : string 
    {
        $attr = array('class' => self::HIDDEN_CLASS);
        $text = get_string('teacher', 'coursework').': ';
        $text.= cg::get_user_photo($this->work->teacher);
        $text.= cg::get_user_name($this->work->teacher);
        return \html_writer::tag('div', $text, $attr);
    }

    private function get_state_cell() : string 
    {
        $attr = array('class' => self::HIDDEN_CLASS);
        $text = get_string('state', 'coursework').': ';
        $text.= cg::get_state_name($this->work->status);
        return \html_writer::tag('div', $text, $attr);
    }

    private function get_grade_cell() : string 
    {
        $attr = array('class' => self::HIDDEN_CLASS);
        $text = get_string('grade', 'coursework').': ';
        $text.= $this->work->grade;
        return \html_writer::tag('div', $text, $attr);
    }


}
