<?php

namespace Coursework\Config\DistributeToLeaders;

use Coursework\Classes\Lib\StudentsMassActions as sma;
use Coursework\Lib as stepLib;

class DistributeStudentsTable extends sma\StudentsTable 
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
            return array();
        }
        else 
        {
            return array('style' => 'color: grey; cursor: not-allowed');
        }
    }
    
    protected function get_select_student_checkbox_cell(\stdClass $student, int $i) : string 
    {
        if(empty($student->teacher))
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

            if(empty($i))
            {
                $input = sma\StepByStep::get_select_explanation($input);
            }
        }
        else 
        {
            $input = '';
        }

        return \html_writer::tag('td', $input);
    }

}
