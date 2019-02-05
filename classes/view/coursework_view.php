<?php


abstract class CourseworkView
{
    protected $cm;
    protected $course;

    protected $name;
    protected $intro;

    protected $students = array();

    public function display() : void
    {
        $str = $this->get_coursework_name();
        $str.= $this->get_coursework_intro();
        $str.= $this->get_coursework_interface();
        $str.= $this->get_back_to_course_button();

        echo $str;
    }

    function __construct($course, $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->database_events_handler();

        $this->initilize_coursework_name_and_intro();

        $this->students = $this->get_coursework_students_database_records();
    }

    // Database functions
    abstract protected function database_events_handler() : void;

    // Constructor functions
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

        for($i = 0; $i < count($this->students); $i++)
        {
            $str.= '<tr>';
            $str.= $this->get_student_name($this->students[$i], $i);
            $str.= $this->get_student_group($this->students[$i], $i);
            $str.= $this->get_leader_cell($this->students[$i], $i);
            $str.= $this->get_course_cell($this->students[$i], $i);
            $str.= $this->get_theme_cell($this->students[$i], $i);
            $str.= $this->get_grade_cell($this->students[$i], $i);
            $str.= $this->get_comment_cell($this->students[$i], $i);
            $str.= $this->get_btn_cell($this->students[$i], $i);
            $str.= '</tr>';
        }

        return $str;
    }

    abstract protected function get_interface_html_form() : string;

    abstract protected function get_student_name($row, $i) : string;

    abstract protected function get_student_group($row, $i) : string;

    abstract protected function get_leader_cell($row, $i) : string;

    abstract protected function get_course_cell($row, $i) : string;

    abstract protected function get_theme_cell($row, $i) : string;

    abstract protected function get_grade_cell($row, $i) : string;

    abstract protected function get_comment_cell($row, $i) : string;

    abstract protected function get_btn_cell($row, $i) : string;

    protected function get_back_to_course_button() : string
    {
        $str = '<br><form action="/course/view.php">';
        $str.= '<input type="hidden" name="id" value="'.$this->course->id.'">';
        $str.= '<button>'.get_string('back_to_course', 'coursework').'</button>';
        $str.= '</form>';
        return $str;
    }

}

