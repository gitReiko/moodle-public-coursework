<?php

use coursework_lib as cw;

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
        $this->students = $this->get_students();
    }

    public function get_gui() : string 
    {
        $gui = $this->get_html_form();
        $gui.= $this->get_overview_header();
        $gui.= $this->get_mass_choice_panel();
        $gui.= $this->get_students_table();
        $gui.= $this->get_distribute_button();
        
        return $gui;
    }

    private function get_students() : array 
    {
        $students = array();
        $archetypesRoles = cw_get_archetype_roles(array('student'));
        $students = cw_get_users_with_archetype_roles_from_group($this->groups, $archetypesRoles, $this->course->id, $this->cm->instance);
        $students = cw_array_unique_for_stdclass($students);
        $students = $this->add_groups_to_students_array($students);
        $students = $this->add_leaders_and_courses_to_students_array($students);

        return $students;
    }

    private function add_groups_to_students_array(array $students) : array 
    {
        $allowedGroups = $this->groups;

        foreach($students as $student)
        {
            $studentGroups = cw\get_user_course_groups($this->course->id, $student->id);

            foreach($studentGroups as $studentGroup)
            {

                if($this->is_user_group_allowed($allowedGroups, $studentGroup))
                {
                    $temp = new stdClass;
                    $temp->id = $studentGroup->id;
                    $temp->name = $studentGroup->name;

                    $student->groups[] = $temp;
                }
            }
        }

        return $students;
    }

    private function is_user_group_allowed(array $allowedGroups, stdClass $userGroup) : bool 
    {
        foreach($allowedGroups as $allowedGroup)
        {
            if($userGroup->id == $allowedGroup->id) return true;
        }

        return false;
    }

    private function add_leaders_and_courses_to_students_array(array $students) : array 
    {
        foreach($students as $student)
        {
            if(empty($student->leader))
            {
                $temp = $this->get_student_leader_and_course($student);

                if(isset($temp->teacher))
                {
                    $student->leader = cw_get_user_name($temp->teacher);
                    $student->course = cw\get_course_fullname($temp->course);
                }
                else
                {
                    $student->leader = '';
                    $student->course = '';                 
                }
            }
        }

        return $students;
    }

    private function get_student_leader_and_course(stdClass $student) 
    {
        global $DB;
        $conditions = array('coursework'=>$this->cm->instance, 'student'=>$student->id);
        return $DB->get_record('coursework_students', $conditions);
    }

    private function get_html_form() : string 
    {
        $form = '<form id="'.self::DISTRIBUTE_FORM.'">';
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

    private function get_mass_choice_panel() : string 
    {
        $panel = '<table><tr>';
        $panel.= '<td>'.get_string('select', 'coursework').'</td>';
        $panel.= '<td>'.$this->get_mass_choice_select().'</td>';
        $panel.= '<td>'.$this->get_cancel_choice_button().'</td>';
        $panel.= '</tr></table>';
        return $panel;
    }

    private function get_mass_choice_select() : string 
    {
        $jsfunct = 'onclick="select_students_checkboxes(this)"';
        $select = '<select autocomplete="off">';
        $select.= "<option value='all' $jsfunct>".get_string('all_participants', 'coursework').'</option>';
        foreach($this->groups as $group)
        {
            $select.="<option value='{$group->name}' $jsfunct>".$group->name.'</option>';
        }
        $select.= '</select>';
        return $select;
    }

    private function get_cancel_choice_button() : string 
    {
        return '<button onclick="unselect_students_checkboxes()">'.get_string('cancel_choice', 'coursework').'</button>';
    }

    private function get_students_table() : string 
    {
        $table = '<table>';
        $table.= $this->get_students_table_header();
        foreach($this->students as $student)
        {
            $table.= '<tr>';
            $table.= '<td>'.$this->get_student_choice_checkbox($student).'</td>';
            $table.= "<td>{$student->fullname}</td>";
            $table.= '<td>'.$this->get_table_data_groupnames($student->groups).'</td>';
            $table.= "<td>{$student->leader}</td>";
            $table.= "<td>{$student->course}</td>";
            $table.= '</tr>';
        }
        $table.= ' </table>';
        return $table;
    }

    private function get_students_table_header() : string 
    {
        $header = '<tr>';
        $header.= '<td></td>';
        $header.= '<td>'.get_string('fullname', 'coursework').'</td>';
        $header.= '<td>'.get_string('group', 'coursework').'</td>';
        $header.= '<td>'.get_string('leader', 'coursework').'</td>';
        $header.= '<td>'.get_string('course', 'coursework').'</td>';
        $header.= '</tr>';
        return $header;
    }

    private function get_student_choice_checkbox(stdClass $student) : string 
    {
        $input = '<input type="checkbox" form="'.self::DISTRIBUTE_FORM.'" autocomplete="off" ';
        $input.= ' name="'.STUDENT.'[]" value="'.$student->id.SEPARATOR.$student->fullname.SEPARATOR.'" ';
        $input.= 'class="students '.$this->get_checkbox_group_classes($student->groups).'">';
        return $input;
    }

    private function get_checkbox_group_classes(array $groups)
    {
        $classes = '';
        foreach($groups as $group) $classes.= $group->name.' ';
        return $classes;
    }

    private function get_table_data_groupnames(array $groups) : string 
    {
        
        if(count($groups) == 1) return reset($groups)->name;
        else
        {
            $td = '';

            foreach($groups as $group)
            {
                $td.= $group->name.'<br>';
            }

            return $td;
        }
    }

    private function get_distribute_button() : string 
    {
        $jsfunc = "onclick='return validate_students_distribution_overview_form()'";
        return "<button form='".self::DISTRIBUTE_FORM."' $jsfunc>".get_string('distribute', 'coursework').'</button>';
    }


}

