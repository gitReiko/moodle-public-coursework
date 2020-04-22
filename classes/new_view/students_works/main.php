<?php

require_once 'data_getters/students_works_getter.php';

use coursework_lib as lib;

class StudentsWorksMain 
{
    private $course;
    private $cm;

    private $students;

    function __construct(stdClass $course, stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $getter = new StudentsWorksGetter($this->course, $this->cm);
        $this->students = $getter->get_available_students();

        print_r($this->students);
    }

    public function get_page() : string 
    {
        $page = $this->get_page_header();
        $page.= $this->get_students_list();
        return $page;
    }

    private function get_page_header() : string 
    {
        return '<h3>'.get_string('student_works_list_header', 'coursework').'</h3>';
    }

    private function get_students_list() : string 
    {
        $list = '<table>';
        $list.= $this->get_students_list_header();
        $list.= '</table>';
        return $list;
    }

    private function get_students_list_header() : string 
    {
        $header = '<thead><tr>';
        $header.= $this->get_student_header();
        $header.= $this->get_leader_header();
        $header.= $this->get_course_header();
        $header.= $this->get_theme_header();

        $header.= '</tr></thead>';
        return $header;
    }

    private function get_student_header() : string 
    {
        return '<td>'.get_string('student', 'coursework').'</td>';
    }

    private function get_leader_header() : string 
    {
        return '<td>'.get_string('leader_short', 'coursework').'</td>';
    }

    private function get_course_header() : string 
    {
        return '<td>'.get_string('course_short', 'coursework').'</td>';
    }

    private function get_theme_header() : string 
    {
        return '<td>'.get_string('theme_short', 'coursework').'</td>';
    }




}