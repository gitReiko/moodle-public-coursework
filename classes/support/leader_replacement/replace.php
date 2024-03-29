<?php

namespace Coursework\Support\LeaderReplacement;

use Coursework\Classes\Lib\StudentsMassActions\Lib as massLib;
use Coursework\Lib\Getters\TeachersGetter as teachGetter;

class Replace 
{
    const FORM_NAME = 'leader_replacement';

    private $course;
    private $cm;

    private $students;

    function __construct(\stdClass $course, \stdClass $cm) 
    {
        $this->course = $course;
        $this->cm = $cm;
        
        $this->students = massLib::get_distribute_students();
    }

    public function get_gui() : string 
    {
        
        $gui = $this->get_change_leader_for_students_header();
        $gui.= $this->get_list_of_the_students();
        $gui.= massLib::get_action_students_inputs($this->students, self::FORM_NAME);
        $gui.= $this->get_leader_header();
        $gui.= $this->get_leader_select();
        $gui.= $this->get_buttons_panel();
        $gui.= $this->get_html_form();

        return $gui;
    }

    private function get_change_leader_for_students_header() : string
    {
        return \html_writer::tag('h3', get_string('change_leader_for_students_header', 'coursework'));
    }

    private function get_list_of_the_students() : string 
    {
        $names = \html_writer::start_tag('p');
        foreach($this->students as $student)
        {
            $names.= $student->fullname.', ';
        }
        $names = mb_substr($names, 0, (mb_strlen($names) - 2));
        $names.= '.'.\html_writer::end_tag('p');;

        return $names;
    }

    private function get_leader_header() : string 
    {
        return \html_writer::tag('h3', get_string('leader', 'coursework'));
    }

    private function get_leader_select() : string
    {
        $leaders = teachGetter::get_users_with_teacher_role($this->cm);

        $select = \html_writer::start_tag('p');
        $attr = array(
            'name' => Main::TEACHER,
            'autocomplete' => 'off',
            'form' => self::FORM_NAME,
            'autofocus' => 'autofocus'
        );
        $select.= \html_writer::start_tag('select', $attr);

        foreach($leaders as $leader)
        {
            $attr = array('value' => $leader->id);
            $text = $leader->fullname;
            $select.= \html_writer::tag('option', $text, $attr);;
        }

        $select.= \html_writer::end_tag('p');
        $select.= \html_writer::end_tag('select');

        return $select;
    }

    private function get_buttons_panel() : string 
    {
        $panel = \html_writer::start_tag('table');
        $panel.= \html_writer::start_tag('tr');
        $panel.= \html_writer::tag('td', $this->get_distribute_button());
        $panel.= \html_writer::tag('td', $this->get_back_button());
        $panel.= \html_writer::end_tag('tr');
        $panel.= \html_writer::end_tag('table');

        return $panel;
    }

    private function get_distribute_button() : string 
    {
        $attr = array('form' => self::FORM_NAME);
        $text = get_string('replace', 'coursework');
        return \html_writer::tag('button', $text, $attr);
    }

    private function get_back_button() : string 
    {
        $attr = array(
            'method' => 'post', 
            'action' => Main::MODULE_URL
        );
        $btn = \html_writer::start_tag('form', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::ID,
            'value' => $this->cm->id
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::GUI_TYPE,
            'value' => Main::OVERVIEW
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $btn.= \html_writer::tag('button', get_string('back', 'coursework'));

        $btn.= \html_writer::end_tag('form');

        return $btn;
    }

    private function get_html_form() : string 
    {
        $attr = array(
            'id' => self::FORM_NAME,
            'method' => 'post',
            'action' => Main::MODULE_URL
        );
        $form = \html_writer::start_tag('form', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::ID,
            'value' => $this->cm->id
        );
        $form.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::GUI_TYPE,
            'value' => Main::OVERVIEW
        );
        $form.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::DATABASE_EVENT,
            'value' => Main::OVERVIEW
        );
        $form.= \html_writer::empty_tag('input', $attr);

        $form.= \html_writer::end_tag('form');

        return $form;
    }



}
