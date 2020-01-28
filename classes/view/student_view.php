<?php

use coursework_lib as cw;

class StudentCourseworkView extends CourseworkView
{
    private $chosenTeacher;
    private $chosenCourse;

    // Constructor functions
    protected function checkExceptions() : void
    {
        if(!$this->is_student_enrolled_in_coursework())
        {
            throw new Exception(get_string('e:student_not_enrolled', 'coursework'));
        }
    }

    private function is_student_enrolled_in_coursework() : bool 
    {
        global $USER;
        $students = array();
        $students = cw_get_coursework_users_with_archetypes_roles(array('student'), $this->cm, $this->course->id);
        foreach($students as $student) if($student->id === $USER->id) return true;
        return false;
    }

    protected function get_coursework_students_database_records() : array
    {
        global $USER;
        $tableRow = cw_get_coursework_student($this->cm->instance, $USER->id);
    
        if(empty($tableRow->student)) 
        {
            $tableRow = new stdClass;
            $tableRow->student = $USER->id;
        }
            
        $tableRow->group = cw_get_user_groups_names($this->course->id, $USER->id);
    
        if(empty($tableRow->id) || empty($tableRow->course))
        {
            $tableRow->data = $this->get_available_teachers();
        }

        if(empty($tableRow->theme) || empty($tableRow->owntheme))
        {
            $tableRow->availableThemes = $this->get_available_themes();
        }
    
        return array($tableRow);
    }

    private function get_available_teachers() : array
    {
        global $DB;
        $teachers = $DB->get_records('coursework_teachers', array('coursework'=>$this->cm->instance));
        $availableTeachers = array();
        foreach($teachers as $teacher)
        {
            if(cw_is_teacher_has_quota($this->cm, $teacher->teacher, $teacher->course))
            {
                $availableTeachers[] = $teacher;
            }
        }
        return $availableTeachers;
    }

    private function get_available_themes() : array
    {
        global $DB;

        $allThemes = $DB->get_records('coursework_themes', array('coursework'=>$this->cm->instance), 'course');
        $usedThemes = $this->get_used_themes();

        $availableThemes = array();
        foreach ($allThemes as $theme)
        {
            $used = false;
            foreach($usedThemes as $usedTheme)
            {
                if($theme->id === $usedTheme)
                {
                    $used = true;
                    break;
                }
            }

            if(!$used) $availableThemes[] = $theme;
        }

        return $availableThemes;
    }

    private function get_used_themes() : array
    {
        global $DB;
        $students = cw_get_students_sql_ids_string($this->cm);
        $sql = "SELECT *
                FROM {coursework_students}
                WHERE coursework = ?
                AND student IN($students)
                ORDER BY course";
        $params = array($this->cm->instance);
        $temp = $DB->get_records_sql($sql, $params);

        $themes = array();
        foreach ($temp as $value)
        {
            if(isset($value->theme)) $themes[] = $value->theme;
        }

        return $themes;
    }

    // GUI functions
    protected function get_coursework_interface() : string 
    {
        if($this->is_unallocated_quota_exist() || $this->is_student_chose_leader())
        {
            $str = '<table class="cw_view">';
            $str.= $this->get_table_header();
            $str.= $this->get_table_body();
            $str.= '</table>';
            $str.= $this->get_interface_html_form();
        }
        else
        {
            $str = cw\get_red_message(get_string('e-sv:quota_ended', 'coursework'));
        }

        return $str;
    }

    private function is_student_chose_leader() : bool
    {
        global $DB, $USER;
        $conditions = array('coursework'=>$this->cm->instance, 'student'=>$USER->id);

        if($DB->record_exists('coursework_students', $conditions)) return true;
        else return false;
    }

    private function is_unallocated_quota_exist() : bool 
    {
        if(empty(reset($this->tableRows)->data)) return false;
        else return true;
    }

    protected function get_interface_html_form() : string
    {
        global $USER;
        $str = '<form id="'.STUDENT_FORM.'">';
        $str.= '<input type="hidden" name="id" value="'.$this->cm->id.'" >';
        $str.= '<input type="hidden" name="'.STUDENT.'" value="'.$USER->id.'">';
        $str.= '</form>';
        return $str;
    }

    protected function get_leader_cell($tableRow, $i) : string
    {
        $str = '<td>';
        if(!empty($tableRow->teacher))
        {
            $str.= cw_get_user_photo($tableRow->teacher).' '.cw_get_user_name((int)$tableRow->teacher);
            $this->chosenTeacher = (int)$tableRow->teacher;
        }
        else
        {
            $str.= $this->get_leaders_select($tableRow);
        }
        $str.='</td>';
        return $str;
    }

    private function get_leaders_select($tableRow) : string
    {
        $unique = $this->get_unique_leaders($tableRow);

        $this->chosenTeacher = reset($unique)->teacher;
        
        $str = '<select class="select" id="selected_teacher" name="'.TEACHER.'" form="'.STUDENT_FORM.'" onchange="change_course_select()" autocomplete="off">';
        foreach($unique as $value)
        {
            $str.= '<option value="'.$value->teacher.'" >'.cw_get_user_name($value->teacher).'</option>';
        }
        $str .= '</select>';

        return $str;
    }

    private function get_unique_leaders($tableRow) : array
    {
        $leaders = array();

        foreach($tableRow->data as $new)
        {
            $unique = true;

            foreach($leaders as $exist) if($new->teacher == $exist->teacher) $unique = false;

            if($unique) $leaders[] = $new;
        }

        return $leaders;
    }

    protected function get_course_cell($tableRow, $i) : string
    {
        $str = '<td id="course_cell">';

        if(!empty($tableRow->course))
        {
            $str.= cw_get_course_name($tableRow->course);
            $this->chosenCourse = $tableRow->course;
        }
        else $str.= $this->get_courses_select($tableRow);

        $str.= '</td>';
        return $str;
    }

    private function get_courses_select($tableRow) : string
    {
        $str = '';
        $firstCourse = true;

        if(count($tableRow->data))
        {
            $str.= '<select class="select" id="selected_course" name="'.COURSE.'" form="'.STUDENT_FORM.'" autocomplete="off" onchange="change_themes_select(this.value)">';
            foreach($tableRow->data as $value)
            {
                if($value->teacher == $this->chosenTeacher)
                {
                    $str.= '<option value="'.$value->course.'" >'.cw_get_course_name($value->course).'</option>';

                    if($firstCourse)
                    {
                        $this->chosenCourse = $value->course;
                        $firstCourse = false;
                    }
                }
            }
            $str .= '</select>';
            
        }
        else $str = get_string('not_available', 'coursework');

        return $str;
    }

    protected function get_theme_cell($tableRow, $i) : string
    {
        $str = '<td id="theme_cell">';
        if(!empty($tableRow->theme)) $str.= cw_get_theme_name($tableRow->theme);
        else if(!empty($tableRow->owntheme)) $str.= $tableRow->owntheme;
        else $str.= $this->get_available_themes_select($tableRow);
        $str.= '</td>';

        return $str;
    }

    private function get_available_themes_select($tableRow) : string
    {
        if(count($tableRow->availableThemes))
        {
            $str = '<p><select id="selected_theme" form="'.STUDENT_FORM.'" ';
            $str.= ' name="'.THEME.'" data-course="'.$this->chosenCourse.'">';
    
            if($this->isThemesOfChosenCourseExist($tableRow))
            {
                foreach ($tableRow->availableThemes as $theme)
                {
                    if($theme->course === $this->chosenCourse)
                    {
                        $str.= '<option value="'.$theme->id.'">'.$theme->name.'</option>';
                    }
                }
            }
            else
            {
                $str.= '<option id="no_available_themes" data-noavailablethemes="true">'.get_string('no_available_themes', 'coursework').'</option>';
            }
    
            $str.= '</select></p>';
        }
        else $str = get_string('not_available', 'coursework');

        $str.= $this->get_own_theme();

        return $str;
    }

    private function isThemesOfChosenCourseExist(stdClass $tableRow) : bool
    {
        foreach ($tableRow->availableThemes as $theme)
        {
            if($theme->course === $this->chosenCourse) return true;
        }
        return false;
    }

    private function get_own_theme() : string
    {
        $str = '<label class="nowrap" id="own_theme_checkbox_label">';
        $str.= '<input type="checkbox" class="nomargin" id="own_theme_checkbox" onclick="process_own_theme_checkbox(this)" autocomplete="off"/>';
        $str.= ' '.get_string('use_own_theme', 'coursework').'</label>';
        $str.= '<p><input id="own_theme_input" type="text" maxlength="255" name="'.OWN_THEME.'" form="'.STUDENT_FORM.'" disabled /></p>';
        return $str;
    }

    protected function get_btn_cell($tableRow, $i) : string
    {
        $str = '<td class="transparent">';

        if(empty($tableRow->teacher))
        {
            $str.= '<input type="hidden" name="'.DB_EVENT.'" value="'.SELECT.THEME.'" form="'.STUDENT_FORM.'">';
            $str.= $this->get_theme_select_button();
        }
        else if(empty($tableRow->theme) && empty($tableRow->owntheme))
        {
            $str.= '<input type="hidden" name="'.DB_EVENT.'" value="'.SELECT.THEME.'" form="'.STUDENT_FORM.'">';
            $str.= '<input type="hidden" name="'.RECORD.ID.'" value="'.$tableRow->id.'" form="'.STUDENT_FORM.'">';
            $str.= $this->get_theme_select_button();
        }

        $str.= '</td>';

        return $str;
    }

    private function get_theme_select_button() : string
    {
        $str = '<button id="select_teacher" form="'.STUDENT_FORM.'" ';
        $str.= ' onclick=" return process_student_coursework_choice()" ';
        $str.= 'title="'.get_string('cant_be_undone', 'coursework').'">';
        $str.= get_string('choose', 'coursework');
        $str.= '</button>';
        return $str;
    }

    protected function get_footer() : string 
    {
        /*
        $str = '';
        // $this->tableRows array have only one element for StudentCourseworkView.
        if(empty(reset($this->tableRows)->course))
        {
            $str.= '<br>'.$this->get_theme_select_button();
        }
        */
        return '';
    }

    protected function prepare_data_for_js() : string
    {   
        $str = '';
        $tableRow = reset($this->tableRows);

        if($this->is_js_data_necessary($tableRow))
        {
            $str.= $this->prepare_available_teachers_with_courses_for_js($tableRow);
            $str.= $this->prepare_available_themes_for_js($tableRow);
        }

        return $str;
    }

   private function is_js_data_necessary(stdClass $tableRow) : bool 
   {
        if(empty($tableRow->teacher) || empty($tableRow->course)) return true;
        else return false;
   }

   private function prepare_available_teachers_with_courses_for_js(stdClass $tableRow) : string
   {
       $str = '';

       if(isset($tableRow->data))
       {
            global $DB;
            foreach($tableRow->data as $value)
            {
                $course = $DB->get_record('course', array('id'=>$value->course), 'fullname');
    
                $str.= '<p class="hidden js_teacher" ';
                $str.= 'data-teacherid="'.$value->teacher.'" ';
                $str.= 'data-teachername="'.cw_get_user_name($value->teacher).'" ';
                $str.= 'data-courseid="'.$value->course.'" ';
                $str.= 'data-coursename="'.$course->fullname.'" ></p>';
            }
       }

       return $str;
   }

   private function prepare_available_themes_for_js(stdClass $tableRow) : string
   {
       $str = '';
       foreach ($tableRow->availableThemes as $theme)
       {
           $str.= '<p class="hidden js_themes" ';
           $str.= 'data-id="'.$theme->id.'"';
           $str.= 'data-name="'.$theme->name.'"';
           $str.= 'data-course="'.$theme->course.'"></p>';
       }

       return $str;
   }
}

