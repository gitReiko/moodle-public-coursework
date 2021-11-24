<?php

namespace Coursework\Support\DeleteStudentCoursework;

use Coursework\Lib\Getters\StudentsGetter as sg;
use Coursework\Lib\Getters\CommonGetter as cg;

class Page 
{
    private $course;
    private $cm;

    private $students;

    private $autofocus = true;

    function __construct($course, $cm)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->students = $this->get_students();
    }

    public function get_page() : string
    {
        $gui = '';

        if(count($this->students))
        {
            $gui.= $this->get_html_form_begin();
            $gui.= $this->get_remove_distribution_header();
            $gui.= $this->get_remove_distribution_table();
            $gui.= $this->get_remove_distribution_button();
            $gui.= $this->get_hidden_input_params();
            $gui.= $this->get_html_form_end();
        }
        else
        {
            $gui.= cw\get_red_message(get_string('no_distributed_students', 'coursework'));
        }

        return $gui;
    }

    private function get_students() 
    {
        $students = sg::get_all_students($this->cm);
        return sg::add_works_to_students($this->cm->instance, $students);
    }

    private function get_html_form_begin() : string 
    {
        $attr = array(
            'onsubmit' => 'return validate_students_removing()',
            'method' => 'post'
        );
        return \html_writer::start_tag('form', $attr);
    }

    private function get_remove_distribution_header() : string 
    {
        $text = get_string('remove_distribution_header', 'coursework');
        return \html_writer::tag('h3', $text);
    }

    private function get_remove_distribution_table() : string 
    {
        $table = \html_writer::start_tag('table');
        $table.= $this->get_remove_distribution_table_header();
        $table.= $this->get_remove_distribution_table_body();
        $table.= \html_writer::end_tag('table');
        return $table;
    }

    private function get_remove_distribution_table_header() : string 
    {
        $header = \html_writer::start_tag('tr');
        $header.= \html_writer::tag('td', '');
        $header.= \html_writer::tag('td', get_string('student', 'coursework'));
        $header.= \html_writer::tag('td', get_string('leader', 'coursework'));
        $header.= \html_writer::tag('td', get_string('course', 'coursework'));
        $header.= \html_writer::tag('td', get_string('theme', 'coursework'));
        $header.= \html_writer::end_tag('tr');

        return $header;
    }

    private function get_remove_distribution_table_body() : string 
    {
        $body = '';

        foreach($this->students as $value)
        {
            $body.= \html_writer::start_tag('tr');
            $body.= \html_writer::tag('td', $this->get_remove_distribution_checkbox($value));
            $body.= \html_writer::tag('td', $this->get_student_name($value));
            $body.= \html_writer::tag('td', $this->get_teacher_name($value));
            $body.= \html_writer::tag('td', $this->get_course_name($value));
            $body.= \html_writer::tag('td', $this->get_theme($value));
            $body.= \html_writer::end_tag('tr');
        }

        return $body;
    }

    private function get_remove_distribution_checkbox(\stdClass $student) : string 
    {
        $attr = array(
            'class' => 'removeCheckbox',
            'type' => 'checkbox',
            'name' => Main::STUDENT_ROW_ID.'[]',
            'value' => $student->id
        );

        if($this->autofocus)
        {
            $attr = array_merge($attr, array('autofocus' => 'autofocus'));
        }

        return \html_writer::empty_tag('input', $attr);
    }

    private function get_student_name(\stdClass $student) : string 
    {
        return $student->lastname.' '.$student->firstname;
    }

    private function get_teacher_name(\stdClass $student) : string 
    {
        if(empty($student->teacher))
        {
            return '';
        }
        else 
        {
            return cg::get_user_name($student->teacher);
        }
    }

    private function get_course_name(\stdClass $student) : string 
    {
        if(empty($student->course))
        {
            return '';
        }
        else 
        {
            return cg::get_course_name($student->course);
        }
    }

    private function get_theme(\stdClass $student) 
    {
        return $student->theme;
    }

    private function get_remove_distribution_button() : string
    {
        $text = get_string('remove_distribution', 'coursework');
        return \html_writer::tag('button', $text);
    }

    private function get_hidden_input_params() : string 
    {

        $attr = array(
            'type' => 'hidden',
            'name' => ID,
            'value' => $this->cm->id
        );
        $params = \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::DB_EVENT,
            'value' => Main::DB_EVENT
        );
        $params.= \html_writer::empty_tag('input', $attr);

        return $params;
    }

    private function get_html_form_end() : string
    {
        return \html_writer::end_tag('form');
    }


}

