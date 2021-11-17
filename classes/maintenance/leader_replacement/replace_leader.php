<?php

namespace Coursework\Maintenance\LeaderReplacement;

use coursework_lib as lib;

/**
 * @todo создать общего родителя для LeaderReplacementAction и DistributeStudents
 */
class ReplaceLeader 
{
    const FORM_NAME = 'leader_replacement';

    private $course;
    private $cm;

    private $students;

    function __construct(\stdClass $course, \stdClass $cm) 
    {
        $this->course = $course;
        $this->cm = $cm;
        
        $this->students = lib\get_distribute_students();
    }

    public function get_gui() : string 
    {
        
        $gui = $this->get_change_leader_for_students_header();
        $gui.= $this->get_list_of_the_students();
        $gui.= $this->get_hidden_students_inputs();
        $gui.= $this->get_leader_header();
        $gui.= $this->get_leader_select();
        $gui.= $this->get_buttons_panel();
        $gui.= $this->get_html_form();

        return $gui;
    }

    private function get_change_leader_for_students_header() : string
    {
        return'<h3>'.get_string('change_leader_for_students_header', 'coursework').'</h3>';
    }

    private function get_list_of_the_students() : string 
    {
        $names = '<p>';
        foreach($this->students as $student)
        {
            $names.= $student->fullname.', ';
        }
        $names = mb_substr($names, 0, (mb_strlen($names) - 2));
        $names.= '.</p>';

        return $names;
    }

    private function get_hidden_students_inputs() : string 
    {
        $inputs = '';
        foreach($this->students as $student)
        {
            $inputs.= '<input type="hidden" name="'.STUDENTS.'[]" value="'.$student->id.'" form="'.self::FORM_NAME.'">';
        }

        return $inputs;
    }

    private function get_leader_header() : string 
    {
        return '<h3>'.get_string('leader', 'coursework').'</h3>';
    }

    private function get_leader_select() : string
    {
        $leaders = lib\get_all_course_teachers($this->cm);

        $select = '<p><select name="'.TEACHER.'" autocomplete="off" form="'.self::FORM_NAME.'" autofocus>';
        foreach($leaders as $leader)
        {
            $select.= "<option value='{$leader->id}'>{$leader->fullname}</option>";
        }
        $select.= '</select></p>';

        return $select;
    }

    private function get_buttons_panel() : string 
    {
        $panel = '<table><tr>';
        $panel.= '<td>'.$this->get_distribute_button().'</td>';
        $panel.= '<td>'.$this->get_back_button().'</td>';
        $panel.= '</tr></table>';
        return $panel;
    }

    private function get_distribute_button() : string 
    {
        return '<button form="'.self::FORM_NAME.'">'.get_string('change', 'coursework').'</button>';
    }

    private function get_back_button() : string 
    {
        $btn = '<form method="post">';
        $btn.= '<input type="hidden" name="'.CONFIG_MODULE.'" value="'.LEADER_CHANGE.'"/>';
        $btn.= '<input type="hidden" name="'.ID.'" value="'.$this->cm->id.'"/>';
        $btn.= '<input type="hidden" name="'.ConfigurationManager::GUI_TYPE.'" value="'.Main::OVERVIEW.'"/>';
        $btn.= '<button>'.get_string('back', 'coursework').'</button>';
        $btn.= '</form>';
        return $btn;
    }

    private function get_html_form() : string 
    {
        $form = '<form id="'.self::FORM_NAME.'" method="post">';
        $form.= '<input type="hidden" name="'.CONFIG_MODULE.'" value="'.LEADER_CHANGE.'"/>';
        $form.= '<input type="hidden" name="'.ID.'" value="'.$this->cm->id.'"/>';
        $form.= '<input type="hidden" name="'.ConfigurationManager::GUI_TYPE.'" value="'.Main::OVERVIEW.'"/>';
        $form.= '<input type="hidden" name="'.ConfigurationManager::DATABASE_EVENT.'" value="'.Main::OVERVIEW.'"/>';
        $form.= '</form>';

        return $form;
    }



}
