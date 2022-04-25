<?php

namespace Coursework\Support\BackToWorkState;

use Coursework\Lib\Getters\StudentsGetter as sg;

class Page 
{
    private $cm;
    private $students;

    function __construct(\stdClass $cm) 
    {
        $this->cm = $cm;
        $this->students = sg::get_all_students($this->cm);
    }

    public function get_page() : string  
    {
        $p = '';
        $p.= $this->get_page_header();
        $p.= $this->get_start_of_form();
        $p.= $this->get_student_selector();
        $p.= $this->get_back_to_work_button();
        $p.= $this->get_necessary_form_params();
        $p.= $this->get_end_of_form();

        return $p;
    }

    private function get_page_header() : string  
    {
        $text = get_string('return_work_for_rework', 'coursework');
        return \html_writer::tag('h3', $text);
    }

    private function get_start_of_form() : string  
    {
        $attr = array('method' => 'post');
        return \html_writer::start_tag('form', $attr);
    }

    private function get_student_selector() : string  
    {
        $attr = array
        (
            'name' => Main::STUDENT_ID,
            'autofocus' => 'autofocus',
            'autocomplete' => 'off',
        );
        $s = \html_writer::start_tag('select', $attr);

        foreach($this->students as $student)
        {
            $attr = array
            (
                'value' => $student->id
            );
            $s.= \html_writer::start_tag('option', $attr);
            $s.= $student->lastname.' '.$student->firstname;
            $s.= \html_writer::end_tag('option');
        }

        $s.= \html_writer::start_tag('select', $attr);
        $s = \html_writer::tag('p', $s);

        return $s;
    }

    private function get_back_to_work_button() : string  
    {
        $text = get_string('return_work_for_rework', 'coursework');
        $button = \html_writer::tag('button', $text);
        return \html_writer::tag('p', $button);
    }

    private function get_necessary_form_params() : string  
    {
        $attr = array
        (
            'type' => 'hidden',
            'name' => Main::COURSEWORK_ID,
            'value' => $this->cm->instance
        );
        $p = \html_writer::empty_tag('input', $attr);

        $attr = array
        (
            'type' => 'hidden',
            'name' => Main::DATABASE_EVENT,
            'value' => Main::RETURN_WORK_FOR_REWORK
        );
        $p.= \html_writer::empty_tag('input', $attr);


        return $p;
    }

    private function get_end_of_form() : string  
    {
        return \html_writer::end_tag('form');
    }

}

