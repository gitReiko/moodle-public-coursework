<?php

namespace Coursework\Support\DeleteStudentCoursework;

use Coursework\Lib\Getters\StudentsGetter as sg;
use Coursework\Lib\Getters\CommonGetter as cg;

class Page 
{
    private $course;
    private $cm;

    private $students;

    function __construct($course, $cm)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->students = $this->get_students();
    }

    public function get_page() : string
    {
        $gui = '';
        $gui.= $this->get_page_header();

        if(count($this->students))
        {
            $gui.= $this->get_html_form_begin();
            $gui.= $this->get_students_table();
            $gui.= $this->get_delete_students_courseworks_button();
            $gui.= $this->get_hidden_input_params();
            $gui.= $this->get_html_form_end();
        }
        else
        {
            $gui.= $this->get_students_not_exists();
        }

        return $gui;
    }

    private function get_students() 
    {
        $students = sg::get_all_students($this->cm);
        return sg::get_students_with_their_works($this->cm->instance, $students);
    }

    private function get_html_form_begin() : string 
    {
        $removeConf = get_string('alert_delete_student_courseworks', 'coursework');
        $attr = array(
            'onsubmit' => 'return confirm_students_removing(`'.$removeConf.'`)',
            'method' => 'post'
        );
        return \html_writer::start_tag('form', $attr);
    }

    private function get_page_header() : string 
    {
        $text = get_string('delete_student_coursework', 'coursework');
        return \html_writer::tag('h3', $text);
    }

    private function get_students_table() : string 
    {
        $attr = array('class' => 'delete_student_coursework');
        $table = \html_writer::start_tag('table', $attr);
        $table.= $this->get_students_table_header();
        $table.= $this->get_students_table_body();
        $table.= \html_writer::end_tag('table');
        return $table;
    }

    private function get_students_table_header() : string 
    {
        $header = \html_writer::start_tag('thead');
        $header.= \html_writer::start_tag('tr');
        $header.= \html_writer::tag('td', '');
        $header.= \html_writer::tag('td', get_string('student', 'coursework'));
        $header.= \html_writer::tag('td', get_string('state', 'coursework'));
        $header.= \html_writer::tag('td', get_string('leader', 'coursework'));
        $header.= \html_writer::tag('td', get_string('course', 'coursework'));
        $header.= \html_writer::tag('td', get_string('theme', 'coursework'));
        $header.= \html_writer::end_tag('tr');
        $header.= \html_writer::end_tag('thead');

        return $header;
    }

    private function get_students_table_body() : string 
    {
        $i = 1;
        $body = \html_writer::start_tag('tbody');;

        foreach($this->students as $student)
        {
            if($this->is_students_select_course($student))
            {
                $body.= $this->get_action_table_row($student, $i);
            }
            else
            {
                $body.= $this->get_empty_table_row($student);
            }

            $i++;
        }

        $body.= \html_writer::end_tag('tbody');

        return $body;
    }

    private function get_action_table_row(\stdClass $student, int $i)
    {
        $attr = array(
            'id' => 'student-row-'.$i,
            'onclick' => 'toggle_student_checkbox(`'.$i.'`)'
        );
        $row = \html_writer::start_tag('tr', $attr);
        $row.= \html_writer::tag('td', $this->get_delete_coursework_checkbox($student, $i));
        $row.= \html_writer::tag('td', $this->get_student_name($student));
        $row.= \html_writer::tag('td', $this->get_state($student));
        $row.= \html_writer::tag('td', $this->get_teacher_name($student));
        $row.= \html_writer::tag('td', $this->get_course_name($student));
        $row.= \html_writer::tag('td', $this->get_theme($student));
        $row.= \html_writer::end_tag('tr');

        return $row;
    }

    private function get_empty_table_row(\stdClass $student)
    {
        $attr = array('class' => 'empty_row');
        $row = \html_writer::start_tag('tr', $attr);
        $row.= \html_writer::tag('td', '');
        $row.= \html_writer::tag('td', $this->get_student_name($student));
        $attr = array('colspan' => 4);
        $row.= \html_writer::tag('td', $this->get_state($student), $attr);
        $row.= \html_writer::end_tag('tr');

        return $row;
    }

    private function get_delete_coursework_checkbox(\stdClass $student, int $i) : string 
    {
        $attr = array(
            'id' => 'checkbox-row-'.$i,
            'class' => 'delete_checkboxes',
            'type' => 'checkbox',
            'name' => Main::STUDENT_ID.'[]',
            'value' => $student->id,
            'autocomplete' => 'off',
            'onclick' => 'toggle_student_checkbox(`'.$i.'`);'
            .'change_row_color(`'.$i.'`);'
        );

        return \html_writer::empty_tag('input', $attr);
    }

    private function get_student_name(\stdClass $student) : string 
    {
        return $student->lastname.' '.$student->firstname;
    }

    private function get_state(\stdClass $student) : string 
    {
        if(!$this->is_students_select_course($student))
        {
            return get_string('leader_not_selected', 'coursework');
        }
        else if(!$this->is_students_select_theme($student))
        {
            return get_string('theme_no_selected', 'coursework');
        }
        else 
        {
            return get_string('work_'.$student->latestStatus, 'coursework');
        }
    }

    private function is_students_select_course(\stdClass $student) : bool 
    {
        if(empty($student->course))
        {
            return false;
        }
        else 
        {
            return true;
        }
    }

    private function is_students_select_theme(\stdClass $student) : bool 
    {
        if(empty($student->theme))
        {
            return false;
        }
        else 
        {
            return true;
        }
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

    private function get_delete_students_courseworks_button() : string
    {
        $attr = array(
            'id' => 'delete_button',
            'title' => get_string('select_student_to_activate_btn', 'coursework')
        );
        $text = get_string('delete_selected_courseworks', 'coursework');
        return \html_writer::tag('button', $text, $attr);
    }

    private function get_hidden_input_params() : string 
    {

        $attr = array(
            'type' => 'hidden',
            'name' => Main::ID,
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

    private function get_students_not_exists()
    {
        $attr = array('class' => 'red-message');
        $text = get_string('no_students_who_started_courseworks', 'coursework');
        return \html_writer::tag('span', $text, $attr);
    }


}

