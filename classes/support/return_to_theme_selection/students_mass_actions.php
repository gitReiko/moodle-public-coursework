<?php

namespace Coursework\Support\ReturnToThemeSelection;

use Coursework\ClassesLib\StudentsMassActions as sma;

class ReplaceStudentsTable extends sma\StudentsTable 
{

    function __construct(array $students, string $formName)
    {
        $this->students = $students;
        $this->formName = $formName;
        parent::__construct($students, $formName);
    }

    protected function get_row_attr($student)
    {
        if(empty($student->teacher))
        {
            return array('style' => 'color: grey; cursor: not-allowed');
        }
        else 
        {
            return array();
        }
    }
    
    protected function get_select_student_checkbox_cell(\stdClass $student) : string 
    {
        if(!empty($student->theme))
        {
            $value = $student->id.self::SEPARATOR.$student->lastname;
            $value.= ' '.$student->firstname.self::SEPARATOR;
    
            $attr = array(
                'type' => 'checkbox',
                'form' => $this->formName,
                'class' => 'students '.$this->get_group_classes($student->groups),
                'name' => self::STUDENTS.'[]',
                'value' => $value,
                'autocomplete' => 'off'
    
            );
            $input = \html_writer::empty_tag('input', $attr);
        }
        else 
        {
            $input = '';
        }

        return \html_writer::tag('td', $input);
    }

}
