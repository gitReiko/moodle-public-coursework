<?php

namespace Coursework\Support\ReturnToThemeSelection;

use Coursework\Classes\Lib\StudentsMassActions as sma;

class ReselectStudentsTable extends sma\StudentsTable 
{

    function __construct(array $students, string $formName)
    {
        $this->students = $students;
        $this->formName = $formName;
        parent::__construct($students, $formName);
    }

    protected function get_custom_header_cells() : string 
    {
        return \html_writer::tag('td', get_string('theme', 'coursework'));
    }

    protected function get_custom_row_cells(\stdClass $student) : string 
    {
        $cells.= $this->get_theme_cell($student->theme);

        return $cells;
    }

    private function get_theme_cell($theme) : string 
    {
        if(empty($theme))
        {
            $text = '';
        }
        else 
        {
            $text = $theme;
        }

        return \html_writer::tag('td', $text);
    }

    protected function get_row_attr($student)
    {
        if(empty($student->theme))
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
