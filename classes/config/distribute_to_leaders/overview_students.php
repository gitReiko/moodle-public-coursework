<?php

namespace Coursework\Config\DistributeToLeaders;

use coursework_lib as cw;
use coursework_students_mass_actions_gui_templates as gui;

class StudentsDistributionOverview 
{
    private $course;
    private $cm;

    private $groups;
    private $students;

    const DISTRIBUTE_FORM = 'distribute_form';

    function __construct(stdClass $course, stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->groups = groups_get_activity_allowed_groups($cm);
        $this->students = cw\get_coursework_students_with_groups_leaders_courses($this->cm, $this->groups);
    }

    public function get_gui() : string 
    {
        $gui = $this->get_html_form();
        $gui.= $this->get_overview_header();
        $gui.= gui\get_mass_choice_selector($this->groups);
        $gui.= gui\get_students_list($this->students, self::DISTRIBUTE_FORM);
        $gui.= $this->get_distribute_button();
        
        return $gui;
    }

    private function get_html_form() : string 
    {
        $form = '<form id="'.self::DISTRIBUTE_FORM.'" method="post">';
        $form.= '<input type="hidden" name="'.CONFIG_MODULE.'" value="'.STUDENTS_DISTRIBUTION.'"/>';
        $form.= '<input type="hidden" name="'.ID.'" value="'.$this->cm->id.'"/>';
        $form.= '<input type="hidden" name="'.ConfigurationManager::GUI_TYPE.'" value="'.StudentsDistribution::DISTRIBUTION.'"/>';
        $form.= '</form>';

        return $form;
    }

    private function get_overview_header() : string 
    {
        return '<h3>'.get_string('sd_overview_header', 'coursework').'</h3>';
    }

    private function get_distribute_button() : string 
    {
        $jsfunc = "onclick='return validate_students_mass_action()'";
        return "<button form='".self::DISTRIBUTE_FORM."' $jsfunc>".get_string('distribute', 'coursework').'</button>';
    }


}

