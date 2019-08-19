<?php

use coursework_lib as cw;

class RemoveDistribution 
{
    private $course;
    private $cm;

    private $students;

    // Constructor functions
    function __construct($course, $cm)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->students = $this->get_students();
    }

    public function execute() : string 
    {
        // database
        
        return $this->get_gui();
    }

    private function get_students() : array
    {
        $students = array();
        $distributedStudents = $this->get_distributed_students();
        $allowedGroups = groups_get_activity_allowed_groups($this->cm);

        foreach($distributedStudents as $dStudent)
        {
            foreach($allowedGroups as $aGroup)
            {
                if(groups_is_member($aGroup->id, $dStudent->student))
                {
                    $students[] = $dStudent;
                    break;
                }
            }
        }

        return $students;
    }

    private function get_distributed_students()
    {
        global $DB;
        $conditions = array('coursework'=>$this->cm->instance);
        return $DB->get_records('coursework_students', $conditions);
    }

    private function get_gui() : string
    {
        $gui = '';

        if(count($this->students))
        {
            $gui.= $this->get_html_form_begin();
            $gui.= $this->get_remove_distribution_header();
            $gui.= $this->get_remove_distribution_table();


            $gui.= $this->get_html_form_end();
        }
        else
        {
            $gui.= cw\get_red_message(get_string('no_distributed_students', 'coursework'));
        }

        return $gui;
    }

    private function get_html_form_begin() : string 
    {
        return '<form>';
    }

    private function get_remove_distribution_header() : string 
    {
        return '<h3>'.get_string('remove_distribution_header', 'coursework').'</h3>';
    }

    private function get_remove_distribution_table() : string 
    {
        $table = '<table>';
        $table.= $this->get_remove_distribution_table_header();
        $table.= $this->get_remove_distribution_table_body();
        $table.= '</table>';
        return $table;
    }

    private function get_remove_distribution_table_header() : string 
    {
        $header = '<tr>';
        $header.= '<td></td>';
        $header.= '<td>'.get_string('student', 'coursework').'</td>';
        $header.= '<td>'.get_string('leader', 'coursework').'</td>';
        $header.= '<td>'.get_string('course', 'coursework').'</td>';
        $header.= '<td>'.get_string('theme', 'coursework').'</td>';
        $header.= '</tr>';
        return $header;
    }

    private function get_remove_distribution_table_body() : string 
    {
        $body = '';

        foreach($this->students as $value)
        {
            $body.= '<tr>';
            $body.= '<td>'.$this->get_remove_distribution_checkbox($value).'</td>';
            $body.= '<td>'.$this->get_student_name($value).'</td>';
            $body.= '<td>'.$this->get_teacher_name($value).'</td>';
            $body.= '<td>'.$this->get_course_name($value).'</td>';
            $body.= '<td>'.$this->get_theme($value).'</td>';
            $body.= '</tr>';
        }

        return $body;
    }

    private function get_remove_distribution_checkbox(stdClass $student) : string 
    {
        return "<input type='checkbox' name='".STUDENT."[]' value='{$student->student}' >";
    }

    private function get_student_name(stdClass $student) : string 
    {
        return cw_get_user_name($student->student);
    }

    private function get_teacher_name(stdClass $student) : string 
    {
        return cw_get_user_name($student->teacher);
    }

    private function get_course_name(stdClass $student) : string 
    {
        return cw\get_course_fullname($student->course);
    }

    private function get_theme(stdClass $student) : string 
    {
        if(!empty($student->theme))
        {
            return cw_get_theme_name($student->theme);
        }
        else
        {
            return $student->owntheme;
        }
    }

    private function get_html_form_end() : string
    {
        return '</form>';
    }


}

