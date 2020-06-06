<?php namespace coursework_students_mass_actions_gui_templates;

function get_mass_choice_selector(array $groups) : string 
{
    $panel = '<table><tr>';
    $panel.= '<td>'.get_string('select', 'coursework').'</td>';
    $panel.= '<td>'.get_mass_choice_options($groups).'</td>';
    $panel.= '<td>'.get_mass_choice_chancel_button().'</td>';
    $panel.= '</tr></table>';
    return $panel;
}

function get_mass_choice_options(array $groups) : string 
{
    $jsfunct = 'onclick="select_students_checkboxes(this)"';
    $select = '<select autocomplete="off">';
    $select.= "<option value='all' $jsfunct>".get_string('all_participants', 'coursework').'</option>';
    foreach($groups as $group)
    {
        $select.="<option value='{$group->name}' $jsfunct>".$group->name.'</option>';
    }
    $select.= '</select>';
    return $select;
}

function get_mass_choice_chancel_button() : string 
{
    return '<button onclick="unselect_students_checkboxes()">'.get_string('cancel_choice', 'coursework').'</button>';
}


function get_students_list(array $students, string $formName) : string 
{
    $table = '<table>';
    $table.= get_students_table_header();
    foreach($students as $student)
    {
        $table.= '<tr>';
        $table.= '<td>'.get_student_choice_checkbox($student, $formName).'</td>';
        $table.= "<td>{$student->fullname}</td>";
        $table.= '<td>'.get_table_data_groupnames($student->groups).'</td>';
        $table.= "<td>{$student->leader}</td>";
        $table.= "<td>{$student->course}</td>";
        $table.= '</tr>';
    }
    $table.= ' </table>';
    return $table;
}

function get_students_table_header() : string 
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

function get_student_choice_checkbox(\stdClass $student, string $formName) : string 
{
    $input = '<input type="checkbox" form="'.$formName.'" autocomplete="off" ';
    $input.= ' name="'.STUDENT.'[]" value="'.$student->id.SEPARATOR.$student->fullname.SEPARATOR.'" ';
    $input.= 'class="students '.get_checkbox_group_classes($student->groups).'">';
    return $input;
}

function get_checkbox_group_classes(array $groups)
{
    $classes = '';
    foreach($groups as $group) $classes.= $group->name.' ';
    return $classes;
}

function get_table_data_groupnames(array $groups) : string 
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





