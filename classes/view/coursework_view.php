<?php

use coursework_lib as lib;

abstract class CourseworkView
{
    protected $cm;
    protected $course;

    protected $name;
    protected $intro;

    protected $tableRows = array();

    protected $stopExecution = false;

    public function display() : void
    {
        $str = '';
        if($this->stopExecution === false)
        {
            $str.= $this->get_coursework_name();
            $str.= $this->get_coursework_intro();
            $str.= $this->get_coursework_interface();
            $str.= $this->get_footer();
            $str.= $this->get_back_to_course_button();
            $str.= $this->prepare_data_for_js();
        }

        echo $str;
    }

    function __construct($course, $cm)
    {
        try
        {
            $this->course = $course;
            $this->cm = $cm;

            $this->check_coursework_configuration();
            $this->checkExceptions();
    
            $this->database_events_handler();
    
            $this->initilize_coursework_name_and_intro();
    
            $this->tableRows = $this->get_coursework_students_database_records();
        }
        catch(Exception $e)
        {
            cw_print_error_message($e->getMessage());
            $this->stopExecution = true;
            return;
        }
    }

    private function check_coursework_configuration() : void 
    {
        if(!$this->is_students_enrolled())
        {
            throw new Exception(get_string('e:students_not_enrolled', 'coursework'));
        }
        if(!$this->is_teachers_enrolled())
        {
            throw new Exception(get_string('e:teachers_not_enrolled', 'coursework'));
        }
    }

    private function is_students_enrolled() : bool 
    {
        $students = array();
        $students = lib\get_coursework_students($this->cm);

        if(count($students)) return true;
        else return false;
    }

    private function is_teachers_enrolled() : bool 
    {
        $teachers = cw_get_coursework_teachers($this->cm->instance);

        if(count($teachers)) return true;
        else return false;
    }

    abstract protected function checkExceptions() : void;

    protected function database_events_handler() : void
    {
        $handler = new ViewDatabaseEventHandler($this->course, $this->cm);
        $handler->execute();
    }

    protected function initilize_coursework_name_and_intro() : void
    {
        global $DB;

        $coursework = $DB->get_record('coursework', array('id'=>$this->cm->instance));

        $this->name = $coursework->name;
        $this->intro = $coursework->intro;
    }

    abstract protected function get_coursework_students_database_records() : array;

    // GUI functions
    protected function get_coursework_name() : string
    {
        return '<h2>'.$this->name.'</h2>';
    }

    protected function get_coursework_intro() : string
    {
        global $DB;
        $coursework = $DB->get_record('coursework', array('id'=> $this->cm->instance));

        return format_module_intro('coursework', $coursework, $this->cm->id).'<br>';
    }

    protected function get_coursework_interface() : string 
    {
        $str = '<table class="cw_view">';
        $str.= $this->get_table_header();
        $str.= $this->get_table_body();
        $str.= '</table>';
        $str.= $this->get_interface_html_form();
        return $str;
    }

    protected function get_table_header() : string
    {
        $str = '<thead><tr>';
        $str.= '<td class="top-left">'.get_string('fullname', 'coursework').'</td>';
        $str.= '<td>'.get_string('group', 'coursework').'</td>';
        $str.= '<td>'.get_string('leader', 'coursework').'</td>';
        $str.= '<td>'.get_string('course', 'coursework').'</td>';
        $str.= '<td>'.get_string('theme', 'coursework').'</td>';
        $str.= '<td>'.get_string('grade', 'coursework').'</td>';
        $str.= '<td class="top-right">'.get_string('comment', 'coursework').'</td>';
        $str.= '<td class="transparent"></td>';
        $str.= '</tr></thead>';
        return $str;
    }

    protected function get_table_body() : string
    {
        $str = '';

        for($i = 0; $i < count($this->tableRows); $i++)
        {
            $str.= '<tr>';
            $str.= $this->get_student_name($this->tableRows[$i], $i);
            $str.= $this->get_student_group($this->tableRows[$i], $i);
            $str.= $this->get_leader_cell($this->tableRows[$i], $i);
            $str.= $this->get_course_cell($this->tableRows[$i], $i);
            $str.= $this->get_theme_cell($this->tableRows[$i], $i);
            $str.= $this->get_grade_cell($this->tableRows[$i], $i);
            $str.= $this->get_comment_cell($this->tableRows[$i], $i);
            $str.= $this->get_btn_cell($this->tableRows[$i], $i);
            $str.= '</tr>';
        }

        return $str;
    }

    // Скорее всего эта функция не должна отличаться в реализациях !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    abstract protected function get_interface_html_form() : string;

    protected function get_student_name($tableRow, $i) : string
    {
        $str = '<td>';
        $str.= cw_get_user_photo($tableRow->student);
        $str.= ' '.cw_get_user_name($tableRow->student);
        $str.= '</td>';
        return $str;
    }

    protected function get_student_group($tableRow, $i) : string
    {
        return '<td>'.$tableRow->group.'</td>';
    }

    protected function get_leader_cell($tableRow, $i) : string
    {
        $str = '<td>';
        if(isset($tableRow->teacher))
        {
            $str.= cw_get_user_photo($tableRow->teacher);
            $str.= ' '.cw_get_user_name($tableRow->teacher);
        }
        else $str .= get_string('not_selected', 'coursework');
        $str.= '</td>';
        return $str;
    }

    protected function get_course_cell($tableRow, $i) : string
    {
        $str = '<td>';
        if(!empty($tableRow->course)) $str .= cw_get_course_name($tableRow->course);
        else $str .= get_string('not_selected', 'coursework');
        $str.= '</td>';
        return $str;
    }

    protected function get_theme_cell($tableRow, $i) : string
    {
        $str = '<td>';
        if(!empty($tableRow->theme)) $str.= cw_get_theme_name($tableRow->theme);
        else if(!empty($tableRow->owntheme)) $str .= $tableRow->owntheme;
        else $str.= get_string('not_selected', 'coursework');
        $str.= '</td>';
        return $str;       
    }

    protected function get_grade_cell($tableRow, $i) : string
    {
        $str = '<td>';
        if(isset($tableRow->grade) && $tableRow->grade) $str .= $tableRow->grade;
        $str.= '</td>';
        return $str;      
    }

    protected function get_comment_cell($tableRow, $i) : string
    {
        $str = '<td>';
        if(isset($tableRow->comment)) $str .= $tableRow->comment;
        $str.= '</td>';
        return $str;
    }

    abstract protected function get_btn_cell($tableRow, $i) : string;

    protected function get_footer() : string 
    {
        return '';
    }

    protected function get_back_to_course_button() : string
    {
        $str = '<br><form action="/course/view.php">';
        $str.= '<input type="hidden" name="id" value="'.$this->course->id.'">';
        $str.= '<button>'.get_string('back_to_course', 'coursework').'</button>';
        $str.= '</form>';
        return $str;
    }

    protected function prepare_data_for_js() : string { return ''; }

}

