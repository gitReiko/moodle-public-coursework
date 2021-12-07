<?php

namespace Coursework\Config\DistributeToLeaders;

use Coursework\ClassesLib\StudentsMassActions as sma;
use Coursework\Lib\Getters\CommonGetter as cg;

class DistributeStudentsTable extends sma\StudentsTable 
{

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
    
    protected function get_select_student_checkbox_cell(\stdClass $student) : string 
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
        }
        else 
        {
            $input = '';
        }

        return \html_writer::tag('td', $input);
    }

    protected function get_leader_cell($leacherId) : string 
    {
        if(empty($leacherId))
        {
            $text = '';
        }
        else 
        {
            $text = cg::get_user_name($leacherId);
        }
        
        return \html_writer::tag('td', $text);
    }

    protected function get_course_cell($courseId) : string 
    {
        if(empty($courseId))
        {
            $text = '';
        }
        else 
        {
            $text = cg::get_course_name($courseId);
        }

        return \html_writer::tag('td', $text);
    }

}
