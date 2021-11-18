<?php

namespace Coursework\Support\LeaderReplacement;

use coursework_lib as lib;
use Coursework\ClassesLib\Students\Mass\Actions\GUI\Template as massActions;

class OverviewStudentsLeaders 
{
    private $course;
    private $cm;

    private $groups;
    private $students;

    const LEADER_REPLACEMENT_FORM = 'leader_replacement_form';

    function __construct(\stdClass $course, \stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->groups = groups_get_activity_allowed_groups($cm);
        $this->students = $this->get_students();
    }

    public function get_gui() : string 
    {
        $gui = $this->get_html_form();
        $gui.= $this->get_overview_header();
        $gui.= massActions\get_mass_choice_selector($this->groups);
        $gui.= massActions\get_students_list($this->students, self::LEADER_REPLACEMENT_FORM);
        $gui.= $this->get_distribute_button();
        
        return $gui;
    }

    private function get_students()
    {
        $students = lib\get_coursework_students_with_groups_leaders_courses($this->cm, $this->groups);
        $students = $this->remove_all_students_without_leader($students);
        return $students;
    }

    private function remove_all_students_without_leader(array $allStudents)
    {
        $studentsWithLeader = array();
        foreach($allStudents as $student)
        {
            if(!empty($student->leader))
            {
                $studentsWithLeader[] = $student;
            }
        }

        return $studentsWithLeader;
    }

    private function get_html_form() : string 
    {
        $attr = array(
            'id' => self::LEADER_REPLACEMENT_FORM,
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
            'value' => Main::LEADER_REPLACEMENT
        );
        $form.= \html_writer::empty_tag('input', $attr);

        $form.= \html_writer::end_tag('form');

        return $form;
    }

    private function get_overview_header() : string 
    {
        return \html_writer::tag('h3', get_string('lc_overview_header', 'coursework'));
    }

    private function get_distribute_button() : string 
    {
        $attr = array(
            'form' => self::LEADER_REPLACEMENT_FORM,
            'onclick' => 'return validate_students_mass_action()'
        );
        $text = get_string('change_leader_for_selected_students', 'coursework');
        return \html_writer::tag('button', $text, $attr);
    }


}

