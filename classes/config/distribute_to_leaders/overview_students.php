<?php

namespace Coursework\Config\DistributeToLeaders;

require_once 'getter.php';

use Coursework\ClassesLib\StudentsMassActions;

class OverviewStudents 
{
    private $course;
    private $cm;

    private $groups;
    private $students;

    const DISTRIBUTE_FORM = 'distribute_form';

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

        $studentsTable = new StudentsMassActions\StudentsTable(
            $this->students, 
            self::DISTRIBUTE_FORM
        );
        $gui.= $studentsTable->get();

        $gui.= $this->get_distribute_button();
        
        return $gui;
    }

    private function get_html_form() : string 
    {
        $attr = array(
            'id' => self::DISTRIBUTE_FORM,
            'method' => 'post'
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
            'value' => Main::DISTRIBUTION
        );
        $form.= \html_writer::empty_tag('input', $attr);

        $form.= \html_writer::end_tag('form');

        return $form;
    }

    private function get_overview_header() : string 
    {
        $text = get_string('sd_overview_header', 'coursework');
        return \html_writer::tag('h3', $text);
    }

    private function get_distribute_button() : string 
    {
        $attr = array(
            'form' => self::DISTRIBUTE_FORM,
            'onclick' => 'return validate_students_mass_action()'
        );
        $text = get_string('distribute', 'coursework');
        return \html_writer::tag('button', $text, $attr);
    }


}

