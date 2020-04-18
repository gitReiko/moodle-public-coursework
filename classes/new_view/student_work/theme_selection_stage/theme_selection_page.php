<?php

require_once 'data_getters/main.php';

use coursework_lib as lib;

class ThemeSelectionPage
{
    private $course;
    private $cm;
    private $studentId;

    private $leaders;
    private $courses;

    function __construct(stdClass $course, stdClass $cm, int $studentId)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->studentId = $studentId;

        $this->init_leaders_and_courses();
    }

    public function get_page() : string 
    {
        $page = $this->get_start_of_html_form();
        $page.= $this->get_theme_selection_header();
        $page.= $this->get_leader_field();
        $page.= $this->get_course_field();

        $page.= $this->get_end_of_html_form();
        $page.= $this->get_js_data();
        return $page;
    }

    private function init_leaders_and_courses() : void 
    {
        $getter = new ThemeSelectionMainGetter($this->course, $this->cm);
        $this->leaders = $getter->get_available_leaders();
        $this->courses = $getter->get_available_courses();
    }

    private function get_start_of_html_form() : string 
    {
        return '<form name="selectForm" >';
    }

    private function get_theme_selection_header() : string 
    {
        return '<h3>'.get_string('view_theme_selection_header', 'coursework').'</p>';
    }

    private function get_leader_field() : string 
    {
        $field = '<h4>'.get_string('leader', 'coursework').'</h4>';
        $field.= $this->get_leaders_select();
        return $field;
    }

    private function get_leaders_select() : string 
    {
        $sel = '<p>';
        $sel.= '<select id="leader_select" ';
        $sel.= ' onchange="SelectThemePage.change_available_courses()"';
        $sel.= ' autocomplete="off">';
        foreach($this->leaders as $leader)
        {
            $sel.= '<option value="'.$leader->id.'">';
            $sel.= $leader->fullname;
            $sel.= '</option>';
        }
        $sel.= '</select>';
        $sel.= '</p>';
        return $sel;
    }

    private function get_course_field() : string 
    {
        $field = '<h4>'.get_string('course', 'coursework').'</h4>';
        $field.= $this->get_courses_select();
        return $field;
    }

    private function get_courses_select() : string 
    {
        $sel = '<p>';
        $sel.= '<select id="course_select" ';
        $sel.= ' autocomplete="off">';
        foreach($this->courses as $course)
        {
            $sel.= '<option class="course_option" value="'.$course->id.'">';
            $sel.= $course->fullname;
            $sel.= '</option>';
        }
        $sel.= '</select>';
        $sel.= '</p>';
        return $sel;
    }

    private function get_js_data() : string 
    {
        $data = $this->get_leaders_js_data();
        $data.= $this->get_courses_js_data();

        return $data;
    }

    private function get_leaders_js_data() : string 
    {
        $data = '';
        foreach($this->leaders as $leader)
        {
            $data.= '<p class="hidden leaders_courses_js" ';
            $data.= ' data-leader="'.$leader->id.'" ';

            $data.= ' data-courses="';
            foreach($leader->courses as $course)
            {
                $data.= $course.' ';
            }
            $data = mb_substr($data, 0, -1);
            $data.= '" ></p>';
        }
        return $data;
    }

    private function get_courses_js_data() : string 
    {
        $data = '';
        foreach($this->courses as $course)
        {
            $data.= '<p class="hidden courses_js" ';
            $data.= ' data-id="'.$course->id.'" ';
            $data.= ' data-fullname="'.$course->fullname.'" ';
            $data.= '" ></p>';
        }
        return $data;
    }


    private function get_end_of_html_form() : string 
    {
        return '</form>';
    }

}
