<?php

class Coursework
{
    private $cm;
    private $course;

    private $name;
    private $intro;

    private $rows = array();

    // Constructor functions
    function __construct($course, $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->database_manager();

        $this->init_coursework_name_and_intro();

        $this->rows = $this->get_rows();
    }

    private function init_coursework_name_and_intro() : void 
    {
        global $DB;

        $coursework = $DB->get_record('coursework', array('id'=>$this->cm->instance));

        $this->name = $coursework->name;
        $this->intro = $coursework->intro;
    }

    private function get_rows() : array
    {
        global $PAGE;

        if(has_capability('mod/coursework:viewfulltable', $PAGE->cm->context))
        {
            echo "tutor";
        }
        else
        {
            return $this->get_student_row();
        }

        return array();
    }

    private function get_student_row() : stdClass 
    {
        global $USER;

        $row = new stdClass();
        $row->name = coursework_get_user_name($USER->id);
        $row->group = $this->get_user_groups($this->course->id, $USER->id);

        $coursework = $this->get_coursework_students($this->cm->instance, $USER->id);
        
        if(isset($coursework) && isset($coursework->id))
        {
            $row->leader = $coursework->tutor;
            $row->course = $coursework->course;
            $row->grade = $coursework->grade;
            $row->comment = $coursework->comment;
        }

        $row->data = $this->get_available_tutors();

        return $row;
    }

    private function get_user_groups(int $course, int $user) : string 
    {
        $str = '';

        $groups = groups_get_user_groups($course, $user);
        
        for($i = 0; $i < count($groups); $i++)
        {
            for($j = 0; $j < count($groups[$i]); $j++)
            {
                $name = groups_get_group_name($groups[$i][$j]);

                if($j) $str .= '<br>';
                $str.= $name;
            }
        }

        return $str;
    }

    private function get_coursework_students(int $coursework, int $student) 
    {
        global $DB;
        $conditions = array('coursework' => $coursework, 'student' => $student);
        return $DB->get_record('coursework_students', $conditions);
    }

    private function get_available_tutors() : array 
    {
        global $DB;

        $all = $DB->get_records('coursework_tutors', array('coursework'=>$this->cm->instance));
        $choosed = $DB->get_records('coursework_students', array('coursework'=>$this->cm->instance));

        foreach($choosed as $uvalue)
        {
            foreach($all as $avalue)
            {
                if(($uvalue->tutor == $avalue->tutor) && ($uvalue->course == $avalue->course))
                {
                    $avalue->quota--;
                    break;
                }
            }
        }

        $available = array();
        foreach($all as $value)
        {
            if($value->quota > 0) $available[] = $value;
        }

        return $available;
    }

    // Public function
    public function execute() : void 
    {
        $str = '';
        $str .= $this->diplay_gui();
        echo $str;
    }

    // Database functions
    private function database_manager() : void 
    {
        global $PAGE;

        if(has_capability('mod/coursework:selecttutor', $PAGE->cm->context))
        {
            $this->insert_coursework_students();
        }
    }

    private function insert_coursework_students() : void 
    {
        global $DB, $USER;

        $create = optional_param(COURSES, 0, PARAM_TEXT);
        $tutor = optional_param(TUTORS, 0, PARAM_INT);
        $course = optional_param(COURSES, 0, PARAM_INT);

        $conditions = array('coursework'=>$this->cm->instance, 'student'=>$USER->id,
                            'tutor'=>$tutor, 'course'=>$course);

        if($create && !$DB->record_exists('coursework_students', $conditions))
        {
            $row = new stdClass();
            $row->coursework = $this->cm->instance;
            $row->student = $USER->id;
            $row->tutor = $tutor;
            $row->course = $course;
            
            $DB->insert_record('coursework_students', $row, false);
        }
    }

    private function diplay_gui() : string
    {
        $str = '<h2>'.$this->name.'</h2>';
        $str.= $this->intro.'<br>';
        $str.= '<form><table class="cw_view">';
        $str.= '<input type="hidden" name="id" value="'.$this->cm->id.'" >';
        $str.= $this->get_table_header();
        $str.= $this->get_table_body();
        $str.= '</table></form>';
        $str.= $this->get_back_to_course_btn();

        return $str;
    }

    private function get_table_header() : string 
    {
        $str = '<tr class="head">';
        $str.= '<td>'.get_string('fullname', 'coursework').'</td>';
        $str.= '<td>'.get_string('group', 'coursework').'</td>';
        $str.= '<td>'.get_string('leader', 'coursework').'</td>';
        $str.= '<td>'.get_string('course', 'coursework').'</td>';
        $str.= '<td>'.get_string('grade', 'coursework').'</td>';
        $str.= '<td>'.get_string('comment', 'coursework').'</td>';
        $str.= '<td class="noborder"></td>';
        $str.= '</tr>';
        return $str;
    }

    private function get_table_body() : string 
    {
        $str = '';

        foreach($this->rows as $row)
        {
            $str.= '<tr>';
            $str.= '<td>'.$row->name.'</td>';
            $str.= '<td>'.$row->group.'</td>';
            $str.= '<td>'.$this->get_leader_cell($row).'</td>';
            $str.= '<td id="course_cell">'.$this->get_course_cell($row).'</td>';
            $str.= '<td>'.$this->get_grade_cell($row).'</td>';
            $str.= '<td>'.$this->get_comment_cell($row).'</td>';
            $str.= '<td class="noborder">'.$this->get_btn_cell($row).'</td>';
            $str.= '</tr>';
        }

        return $str;
    }



    private function get_leader_cell($row) : string 
    {
        $str = '';
        if(isset($row->leader)) $str .= coursework_get_user_name($row->leader);
        else
        {
            global $PAGE;
            if(has_capability('mod/coursework:selecttutor', $PAGE->cm->context))
            {
                $str .= $this->prepare_js_data($row);
                $str .= $this->get_leaders_select($row);
            }
        }
        return $str;
    }

    private function prepare_js_data($row) : string 
    {
        global $DB;
        $str = '';
        foreach($row->data as $value)
        {
            $course = $DB->get_record('course', array('id'=>$value->course), 'fullname');

            $str.= '<p class="hidden js_tutors" ';
            $str.= 'data-tutorid="'.$value->tutor.'" ';
            $str.= 'data-tutorname="'.coursework_get_user_name($value->tutor).'" ';
            $str.= 'data-courseid="'.$value->course.'" ';
            $str.= 'data-coursename="'.$course->fullname.'" ></p>';
        }

        return $str;
    }

    private function get_leaders_select($row) : string 
    {
        $unique = $this->get_unique_leaders($row);

        $str = '<select class="select" id="selected_tutor" name="'.TUTORS.'" ';
        $str.= ' onchange="change_courses_bar()" autocomplete="off">';
        foreach($unique as $value)
        {
            $str.= '<option value="'.$value->tutor.'" >';
            $str.= coursework_get_user_name($value->tutor);
            $str.= '</option>';
        }
        $str .= '</select>';

        return $str;
    }

    private function get_unique_leaders($row) : array 
    {
        $leaders = array();

        foreach($row->data as $new)
        {
            $unique = true;

            foreach($leaders as $exist)
            {
                if($new->tutor == $exist->tutor)
                {
                    $unique = false;
                }
            }
            
            if($unique) $leaders[] = $new;
        }

        return $leaders;
    }

    private function get_course_cell($row) : string 
    {
        $str = '';
        if(isset($row->course))
        {
            global $DB;
            $course = $DB->get_record('course', array('id'=>$row->course), 'fullname');
            $str .= $course->fullname;
        }
        else
        {
            global $PAGE;
            if(has_capability('mod/coursework:selecttutor', $PAGE->cm->context))
            {
                $str .= $this->get_courses_select($row);
            }
        }

        return $str;
    }

    private function get_courses_select($row) : string 
    {
        global $DB;
        $tutor = reset($row->data)->tutor;

        $str = '<select class="select" id="selected_course" name="'.COURSES.'" autocomplete="off">';
        foreach($row->data as $value)
        {
            if($value->tutor == $tutor)
            {
                $course = $DB->get_record('course', array('id'=>$value->course), 'id, fullname');

                $str.= '<option value="'.$value->course.'" >';
                $str.= $course->fullname.'</option>';
            }
        }
        $str .= '</select>';

        return $str;
    }

    private function get_grade_cell($row) : string 
    {
        $str = '';
        if(isset($row->grade) && $row->grade) $str .= $row->grade;
        return $str;
    }

    private function get_comment_cell($row) : string 
    {
        $str = '';
        if(isset($row->comment)) $str .= $row->comment;
        return $str;
    }

    private function get_btn_cell($row) : string 
    {
        global $PAGE;
        $str = '';

        if(has_capability('mod/coursework:selecttutor', $PAGE->cm->context))
        {
            $str.= $this->get_select_tutor_btn($row);
        }

        return $str;
    }

    private function get_select_tutor_btn($row) : string 
    {
        $str = '';
        
        if(!isset($row->leader))
        {
            $str.= '<input type="hidden" name="'.ECM_SELECT_TUTOR.'" value="'.ECM_SELECT_TUTOR.'" >';
            $str.= '<button class="button" ';
            $str.= 'title="'.get_string('cant_be_undone', 'coursework').'">';
            $str.= get_string('select_tutor', 'coursework');
            $str.= '</button>';
            
        }

        return $str;
    }

    private function get_back_to_course_btn() : string 
    {
        $str = '<br><form action="/course/view.php">';
        $str.= '<input type="hidden" name="id" value="'.$this->course->id.'">';
        $str.= '<button>'.get_string('back_to_course', 'coursework').'</button>';
        $str.= '</form>';
        return $str;
    }









}








