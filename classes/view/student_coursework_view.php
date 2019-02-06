<?php


class StudentCourseworkView extends CourseworkView
{
    private $chosenCourse;

    function __construct($course, $cm)
    {
        parent::__construct($course, $cm);
    }

    // Database functions
    protected function database_events_handler() : void
    {
        $selectTutor = optional_param(ECM_SELECT_TUTOR, 0, PARAM_TEXT); // Eto pravilno????
        $selectCourse = optional_param(ECM_SELECT_COURSE, 0, PARAM_TEXT);

        if($selectTutor && !$this->is_coursework_students_exist())
        {
            $this->insert_coursework_students();
        }
        else if($selectCourse)
        {
            $this->update_coursework_students();
        }
    }

    private function is_coursework_students_exist() : bool
    {
        global $DB, $USER;

        $conditions = array('coursework'=>$this->cm->instance, 'student'=>$USER->id);

        if($DB->record_exists('coursework_students', $conditions)) return true;
        else return false;
    }

    private function insert_coursework_students() : void
    {
        global $DB, $USER;

        $tutor = optional_param(TUTORS, 0, PARAM_INT);
        $course = optional_param(COURSES, 0, PARAM_INT);
        $theme = optional_param(SELECT.THEME, 0, PARAM_INT);
        $ownTheme = optional_param(OWN_THEME, 0, PARAM_TEXT);

        if($tutor && $course)
        {
            $row = new stdClass();
            $row->coursework = $this->cm->instance;
            $row->student = $USER->id;
            $row->tutor = $tutor;
            $row->course = $course;
            $row->theme = $theme;
            $row->owntheme = $ownTheme;

            if($this->is_theme_used($theme))
            {
                $this->error_message(get_string('error_theme_already_used', 'coursework'));
            }
            else if($this->is_tutor_quota_over($tutor, $course))
            {
                $this->error_message(get_string('error_tutor_quota_over', 'coursework'));
            }
            else
            {
                if($DB->insert_record('coursework_students', $row)) $this->send_message($tutor);
                else $this->error_message(get_string('error_no_tutor_or_course','coursework'));
            }
        }
    }

    private function update_coursework_students() : void
    {
        global $DB, $USER;

        $course = optional_param(COURSES, 0, PARAM_INT);
        $theme = optional_param(SELECT.THEME, 0, PARAM_INT);
        $ownTheme = optional_param(OWN_THEME, null, PARAM_TEXT);

        $temp = $this->get_coursework_students_id_and_tutor();
        $temp->course = $course;
        $temp->theme = $theme;
        $temp->owntheme = $ownTheme;

        if($this->is_theme_used($temp->theme))
        {
            $this->error_message(get_string('error_theme_already_used', 'coursework'));
        }
        else if($this->is_tutor_quota_over($temp->tutor, $temp->course))
        {
            $this->error_message(get_string('error_tutor_quota_over', 'coursework'));
        }
        else
        {
            if($DB->update_record('coursework_students', $temp)) $this->send_message($temp->tutor);
        }

    }

    private function get_coursework_students_id_and_tutor() : stdClass
    {
        global $DB, $USER;
        $conditions = array('coursework'=>$this->cm->instance, 'student'=>$USER->id);
        return $DB->get_record('coursework_students', $conditions, 'id, student, tutor');
    }

    private function is_theme_used(int $themeID) : bool
    {
        if($themeID)
        {
            global $DB;
            $conditions = array('coursework'=>$this->cm->instance, 'theme'=> $themeID);

            if($DB->record_exists('coursework_students',$conditions)) return true;
            else return false;
        }
        else return false;
    }

    private function is_tutor_quota_over(int $tutor, int $course) : bool
    {
        global $DB;

        $conditions = array('coursework'=>$this->cm->instance,'tutor'=>$tutor,'course'=>$course);
        $tutor = $DB->get_record('coursework_tutors', $conditions);
        $tutorsCount = $DB->count_records('coursework_students', $conditions);

        if($tutorsCount >= $tutor->quota) return true;
        else return false;
    }

    private function error_message(string $message) : void
    {
        echo '<p style="background-color:LightCoral; padding:10px;">'.$message.'</p>';
    }

    // Message functions
    private function send_message($tutor) : void
    {
        global $CFG, $USER;

        $message = new \core\message\message();
        $message->component = 'mod_coursework';
        $message->name = 'tutorselected';
        $message->userfrom = $USER;
        $message->userto = $tutor;
        $message->subject = get_string('tutorselected:head','coursework');
        $message->fullmessage = get_string('tutorselected:head','coursework');
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml = $this->get_html_message();
        $message->smallmessage = get_string('tutorselected:head','coursework');
        $message->notification = '1';
        $message->contexturl = $CFG->wwwroot.'/coursework/view.php?id='.$this->cm->id;
        $message->contexturlname = cw_get_coursework_name($this->cm->instance);
        $message->courseid = $this->course->id;

        message_send($message);
    }

    private function get_html_message() : string
    {
        $params = cw_prepare_data_for_message();
        $message = get_string('tutor_message','coursework', $params);
        $notification = get_string('answer_not_require', 'coursework');

        return cw_get_html_message($this->cm, $this->course->id, $message, $notification);
    }

    // Constructor functions
    protected function get_coursework_students_database_records() : array
    {
        global $USER;

        $row = new stdClass();
        $row->student = $USER->id;
        $row->group = cw_get_user_groups_names($this->course->id, $USER->id);

        $coursework = cw_get_coursework_student($this->cm->instance, $USER->id);

        if(isset($coursework) && isset($coursework->id))
        {
            $row->tutor = $coursework->tutor;
            $row->course = $coursework->course;
            $row->grade = $coursework->grade;
            $row->comment = $coursework->comment;

            if(isset($coursework->theme) && $coursework->theme)
            {
                $row->themeID = $coursework->theme;
                $row->themeName = $this->get_theme_name($row->themeID);
            }
            else if(isset($coursework->owntheme))
            {
                $row->themeID = null;
                $row->themeName = $coursework->owntheme;
            }
            else if(empty($row->course))
            {
                $row->data = $this->get_available_tutors();
                $row->availableThemes = $this->get_available_themes();
            }
        }
        else
        {
            $row->data = $this->get_available_tutors();
            $row->availableThemes = $this->get_available_themes();
        }



        return array($row);
    }

    private function get_theme_name($id) : string
    {
        global $DB;

        $theme = $DB->get_record('coursework_themes', array('id'=>$id));

        if(isset($theme->name)) return $theme->name;
    }

    private function get_available_tutors() : array
    {
        global $DB;
        $tutors = $DB->get_records('coursework_tutors', array('coursework'=>$this->cm->instance));
        $availableTutors = array();
        foreach($tutors as $tutor)
        {
            if($this->is_tutor_has_quota_for_course($tutor))
            {
                $availableTutors[] = $tutor;
            }
        }
        return $availableTutors;
    }

    private function is_tutor_has_quota_for_course(stdClass $tutor) : bool
    {
        global $DB;
        $conditions = array('coursework'=>$this->cm->instance, 'tutor'=>$tutor->tutor, 'course'=>$tutor->course);
        $usedQuota = $DB->count_records('coursework_students', $conditions);

        if(($tutor->quota - $usedQuota) > 0) return true;
        else return false;
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

        $temp = $DB->get_records('coursework_students', array('coursework'=>$this->cm->instance), 'course');
        $themes = array();
        foreach ($temp as $value)
        {
            if(isset($value->theme)) $themes[] = $value->theme;
        }
        return $themes;
    }

    // GUI functions
    protected function get_interface_html_form() : string
    {
        $str = '<form id="'.STUDENT_FORM.'">';
        $str.= '<input type="hidden" name="id" value="'.$this->cm->id.'" >';
        $str.= '</form>';
        return $str;
    }

    protected function get_leader_cell($row, $i) : string
    {
        $str = '<td>';
        if(isset($row->tutor))
        {
            $str.= cw_get_user_photo($row->tutor);
            $str .= ' '.cw_get_user_name((int)$row->tutor);
        }
        else
        {
            $str .= $this->prepare_js_data($row);
            $str .= $this->get_leaders_select($row);
        }
        $str.='</td>';
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
            $str.= 'data-tutorname="'.cw_get_user_name($value->tutor).'" ';
            $str.= 'data-courseid="'.$value->course.'" ';
            $str.= 'data-coursename="'.$course->fullname.'" ></p>';
        }

        $str.= $this->prepare_available_themes_for_js($row);

        return $str;
    }

    private function prepare_available_themes_for_js($row) : string
    {
        $str = '';
        foreach ($row->availableThemes as $theme)
        {
            $str.= '<p class="hidden js_themes" ';
            $str.= 'data-id="'.$theme->id.'"';
            $str.= 'data-name="'.$theme->name.'"';
            $str.= 'data-course="'.$theme->course.'"></p>';
        }

        return $str;
    }

    private function get_leaders_select($row) : string
    {
        $unique = $this->get_unique_leaders($row);

        if(count($unique))
        {
            $str = '<select class="select" id="selected_tutor" name="'.TUTORS.'" ';
            $str.= ' form="'.STUDENT_FORM.'" onchange="change_course_select()" autocomplete="off">';
            foreach($unique as $value)
            {
                $str.= '<option value="'.$value->tutor.'" >';
                $str.= cw_get_user_name($value->tutor);
                $str.= '</option>';
            }
            $str .= '</select>';
        }
        else $str = get_string('no_leaders', 'coursework');

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

    protected function get_course_cell($row, $i) : string
    {
        $str = '<td id="course_cell">';
        if(!empty($row->course))
        {
            global $DB;
            $course = $DB->get_record('course', array('id'=>$row->course), 'fullname');
            $str .= $course->fullname;
        }
        else
        {
            $str .= $this->prepare_js_data($row);
            $str .= $this->get_courses_select($row);
        }
        $str.= '</td>';
        return $str;
    }

    private function get_courses_select($row) : string
    {
        global $DB;
        $firstCourse = true;

        if(count($row->data))
        {
            $tutor = reset($row->data)->tutor;

            $str = '<select class="select" id="selected_course" name="'.COURSES.'" ';
            $str.= ' form="'.STUDENT_FORM.'" autocomplete="off" onchange="change_themes_select(this.value)">';
            foreach($row->data as $value)
            {
                if($value->tutor == $tutor)
                {
                    $course = $DB->get_record('course', array('id'=>$value->course), 'id, fullname');

                    $str.= '<option value="'.$value->course.'" >';
                    $str.= $course->fullname.'</option>';

                    if($firstCourse)
                    {
                        $this->chosenCourse = $value->course;
                        $firstCourse = false;
                    }
                }
            }
            $str .= '</select>';
        }
        else $str = '';

        return $str;
    }


    protected function get_theme_cell($row, $i) : string
    {
        $str = '<td id="theme_cell">';
        if(isset($row->themeName)) $str.= $row->themeName;
        else $str.= $this->get_available_themes_select($row);
        $str.= '</td>';

        return $str;
    }

    private function get_available_themes_select($row) : string
    {
        $str = '<select id="selected_theme" form="'.STUDENT_FORM.'" ';
        $str.= ' name="'.SELECT.THEME.'" data-course="'.$this->chosenCourse.'">';

        if($this->isThemesOfChosenCourseExist($row))
        {
            foreach ($row->availableThemes as $theme)
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

        $str.= '</select>';

        $str.= $this->get_own_theme();

        return $str;
    }

    private function isThemesOfChosenCourseExist(stdClass $row) : bool
    {
        foreach ($row->availableThemes as $theme)
        {
            if($theme->course === $this->chosenCourse) return true;
        }
        return false;
    }

    private function get_own_theme() : string
    {
        $str = '<label class="nowrap" id="own_theme_checkbox_label">';
        $str.= '<input type="checkbox" class="nomargin" id="own_theme_checkbox" onclick="process_own_theme_checkbox(this)" autocomplete="off"/>';
        $str.= get_string('use_own_theme', 'coursework').'</label>';
        $str.= '<input id="own_theme_input" type="text" maxlength="255" name="'.OWN_THEME.'" form="'.STUDENT_FORM.'" disabled />';
        return $str;
    }

    protected function get_btn_cell($row, $i) : string
    {
        $str = '<td class="transparent">';

        if(empty($row->tutor))
        {
            $str.= '<input type="hidden" name="'.ECM_SELECT_TUTOR.'" value="'.ECM_SELECT_TUTOR.'" form="'.STUDENT_FORM.'">';
            $str.= $this->get_theme_select_button();
        }
        else if(empty($row->course))
        {
            $str.= '<input type="hidden" name="'.ECM_SELECT_COURSE.'" value="'.ECM_SELECT_COURSE.'" form="'.STUDENT_FORM.'">';
            $str.= $this->get_theme_select_button();
        }

        $str.= '</td>';

        return $str;
    }

    private function get_theme_select_button() : string
    {
        $str = '<button id="select_tutor" form="'.STUDENT_FORM.'" ';
        $str.= ' onclick=" return process_student_coursework_choice()" ';
        $str.= 'title="'.get_string('cant_be_undone', 'coursework').'">';
        $str.= get_string('make_choice', 'coursework');
        $str.= '</button>';
        return $str;
    }

}

