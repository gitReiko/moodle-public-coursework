<?php

namespace Coursework\Config\AppointLeaders;

use Coursework\Lib\Getters\TeachersGetter as tg;

abstract class Action
{
    protected $course;
    protected $cm;

    protected $courseTeachers;
    protected $siteCourses;

    const ACTION_FORM = 'action_form';

    function __construct($course, $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->courseTeachers = tg::get_users_with_teacher_role($this->cm);
        $this->siteCourses = $this->get_all_site_courses();
    }

    public function display() : string 
    {
        $gui = $this->get_html_form_start();
        $gui.= $this->get_action_header();
        $gui.= StepByStep::get_help_button();
        $gui.= StepByStep::get_leaders_list_explanation($this->get_leader_label());
        $gui.= $this->get_leader_select();
        $gui.= StepByStep::get_courses_list_explanation($this->get_course_label());
        $gui.= $this->get_course_select();
        $gui.= StepByStep::get_quota_explanation($this->get_quota_label());
        $gui.= $this->get_quota_input();
        $gui.= $this->get_form_common_params();
        $gui.= $this->get_form_special_params();
        $gui.= $this->get_html_form_end();
        $gui.= $this->get_buttons_panel();
     
        return $gui;
    }

    private function get_all_site_courses() : array
    {
        global $DB;
        $courses = array();
        $courses = $DB->get_records('course', array(), 'fullname', 'id, fullname');
        return $courses;
    }

    private function get_html_form_start() : string 
    {
        $attr = array('id' => self::ACTION_FORM, 'method' => 'post');
        return \html_writer::start_tag('form', $attr);
    }

    abstract protected function get_action_header() : string;

    private function get_label(string $text, string $title = null) : string 
    {
        if($title) $attr = array('title' => $title);
        else $attr = array();

        return \html_writer::tag('h4', $text, $attr);
    }

    private function get_leader_label() : string 
    {
        $text = get_string('leader', 'coursework');
        $title = get_string('leader_explanation', 'coursework').' ';
        $title.= get_string('only_teachers_in_list', 'coursework');
        return $this->get_label($text, $title);
    }

    protected function get_select(array $items, string $selectName, int $selectedItem = null, bool $autofocus = false) : string 
    {
        $select = \html_writer::start_tag('p');

        $attr = array(
            'name' => $selectName,
            'autocomplete' => 'off'
        );
        if($autofocus)
        {
            $attr = array_merge($attr, array('autofocus' => 'autofocus'));
        }

        $select.= \html_writer::start_tag('select', $attr);
        $select.= $this->get_select_options($items, $selectedItem);
        $select.= \html_writer::end_tag('select');
        $select.= \html_writer::end_tag('p');

        return $select;
    }

    private function get_select_options(array $items, $selectedItem)
    {
        $options = '';

        foreach($items as $item)
        {
            $attr = array('value' => $item->id);

            if($selectedItem == $item->id)
            {
                $attr = array_merge($attr, array('selected' => 'selected'));
            }

            $text = $item->fullname;

            $options.= \html_writer::tag('option', $text, $attr);
        }

        return $options;
    }

    abstract protected function get_leader_select() : string;

    private function get_course_label() : string 
    {
        $text = get_string('course', 'coursework');
        $title = get_string('leader_course_explanation', 'coursework').' ';
        $title.= get_string('in_list_all_site_courses', 'coursework');
        return $this->get_label($text, $title);
    }

    abstract protected function get_course_select() : string;

    private function get_quota_label() : string 
    {
        $text = get_string('quota', 'coursework');
        $title = get_string('quota_explanation', 'coursework');
        return $this->get_label($text, $title);
    }

    abstract protected function get_quota_input() : string;

    private function get_form_common_params() : string 
    {
        $attr = array(
            'type' => 'hidden',
            'name' => 'id',
            'value' => $this->cm->id
        );
        $params = \html_writer::empty_tag('input', $attr);

        return $params;
    }

    abstract protected function get_form_special_params() : string;

    private function get_buttons_panel() : string 
    {
        $attr = array('class' => 'btns_panel');
        $btns = \html_writer::start_tag('table', $attr);
        $btns.= \html_writer::start_tag('tr');
        $btns.= \html_writer::tag('td', $this->get_action_button());
        $btns.= \html_writer::tag('td', $this->get_back_to_overview_button());
        $btns.= \html_writer::end_tag('tr');
        $btns.= \html_writer::end_tag('table');

        return $btns;
    }

    abstract protected function get_action_button() : string ;

    private function get_back_to_overview_button() : string 
    {
        $btn = \html_writer::start_tag('p');

        $attr = array('method' => 'post');
        $btn.= \html_writer::start_tag('form', $attr);

        $attr = array(
            'type' => 'submit',
            'value' => get_string('back', 'coursework')
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => 'id',
            'value' => $this->cm->id
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::GUI_TYPE,
            'value' => Main::OVERVIEW
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $btn.= \html_writer::end_tag('form');
        $btn.= \html_writer::end_tag('p');

        return $btn;
    }

    private function get_html_form_end() : string { return '</form>'; }
    
} 

