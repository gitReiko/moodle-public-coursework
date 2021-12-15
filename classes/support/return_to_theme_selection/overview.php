<?php

namespace Coursework\Support\ReturnToThemeSelection;

require_once 'students_mass_actions.php';
require_once 'getter.php';

use Coursework\ClassesLib\StudentsMassActions;
use Coursework\Lib\Getters\StudentsGetter as sg;
use Coursework\Lib\Getters\CommonGetter as cg;

class Overview 
{
    private $course;
    private $cm;

    private $groups;
    private $students;

    const RETURN_TO_FORM = 'return to theme selection form';

    function __construct(\stdClass $course, \stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $getter = new Getter($this->course, $this->cm);
        $this->groups = $getter->get_groups();
        $this->students = $getter->get_students();
    }

    public function get_gui() : string 
    {
        $gui = $this->get_html_form();
        $gui.= $this->get_overview_header();

        $studentsSelector = new StudentsMassActions\StudentsSelector($this->groups);
        $gui.= $studentsSelector->get();

        $table = new ReselectStudentsTable(
            $this->students, 
            self::RETURN_TO_FORM
        );
        $gui.= $table->get();

        $gui.= $this->get_distribute_button();
        
        return $gui;
    }

    private function get_html_form() : string 
    {
        $confText = get_string('confirm_theme_reselect', 'coursework');

        $attr = array(
            'id' => self::RETURN_TO_FORM,
            'method' => 'post',
            'onsubmit' => 'return confirm_theme_reselect(`'.$confText.'`)',
        );
        $form = \html_writer::start_tag('form', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::ID,
            'value' => $this->cm->id
        );
        $form.= \html_writer::empty_tag('input', $attr);

        $form.= \html_writer::end_tag('form');

        return $form;
    }

    private function get_overview_header() : string 
    {
        return \html_writer::tag('h3', get_string('theme_reselect_header', 'coursework'));
    }

    private function get_distribute_button() : string 
    {
        $attr = array(
            'form' => self::RETURN_TO_FORM,
            'onclick' => 'return validate_students_mass_action()'
        );
        $text = get_string('cancel_theme_select', 'coursework');
        return \html_writer::tag('button', $text, $attr);
    }


}

