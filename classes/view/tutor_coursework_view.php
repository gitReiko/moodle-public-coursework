<?php

class TutorCourseworkView extends CourseworkView
{

    // Database functions
    protected function database_events_handler() : void
    {
        $update = optional_param(ECM_GRADE_STUDENT, 0, PARAM_TEXT);
        $courseworkid = $this->get_coursework_students_id();

        if($update && $courseworkid)
        {
            $this->update_coursework_students($courseworkid);
        }
    }

    private function get_coursework_students_id() : int
    {
        global $DB;
        $student = optional_param(ECM_STUDENTS, 0, PARAM_INT);
        $conditions = array('coursework'=>$this->cm->instance, 'student'=>$student);
        $coursework = $DB->get_record('coursework_students', $conditions);

        if(isset($coursework->id) && $coursework->id) return $coursework->id;
        else return 0;
    }

    private function update_coursework_students($courseworkid) : void
    {
        global $DB;

        $grade = optional_param(ECM_GRADE, 0, PARAM_INT);
        $comment = optional_param(ECM_COMMENT, 0, PARAM_TEXT);

        $sql = 'UPDATE {coursework_students} SET grade = ?, comment = ? WHERE id = ?';
        $params = array($grade, $comment, $courseworkid);

        if($DB->execute($sql, $params))
        {
            $this->send_message();
        }
    }

    // Message functions
    private function send_message() : void
    {
        global $CFG, $USER;

        $message = new \core\message\message();
        $message->component = 'mod_coursework';
        $message->name = 'studentgraded';
        $message->userfrom = $USER;
        $message->userto = optional_param(ECM_STUDENTS, 0, PARAM_INT);
        $message->subject = get_string('studentgraded:head','coursework');
        $message->fullmessage = get_string('studentgraded:head','coursework');
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml = $this->get_html_message();
        $message->smallmessage = get_string('studentgraded:head','coursework');
        $message->notification = '1';
        $message->contexturl = $CFG->wwwroot.'/coursework/view.php?id='.$this->cm->id;
        $message->contexturlname = cw_get_coursework_name($this->cm->instance);
        $message->courseid = $this->course->id;

        message_send($message);
    }

    private function get_html_message() : string
    {
        global $USER;

        $message = '<p>';
        $message.= get_string('user','coursework').' '.cw_get_user_name($USER->id).' ';
        $message.= date('d-m-Y').get_string('at_time','coursework').date('G:i');
        $message.= get_string('studentgraded','coursework');
        $message.= '</p>';

        $notifications = '<p>'.get_string('grade_isnt_final', 'coursework').'</p>';
        $notifications.= '<p>'.get_string('answer_not_require', 'coursework').'</p>';

        return cw_get_html_message($this->cm, $this->course->id, $message, $notifications);
    }

    // Constructor functions
    protected function get_coursework_students_database_records() : array
    {
        global $USER;

        $students = $this->get_coursework_students();
        $rows = array();

        foreach($students as $student)
        {
            $row = new stdClass();
            $row->student = $student->id;
            $row->group = cw_get_user_groups_names($this->course->id, $student->id);

            $coursework = cw_get_coursework_students($this->cm->instance, $student->id);

            if(isset($coursework) && isset($coursework->id))
            {
                $row->leader = $coursework->tutor;
                $row->course = $coursework->course;
                $row->grade = $coursework->grade;
                $row->theme = $coursework->theme;
                $row->owntheme = $coursework->owntheme;
                $row->comment = $coursework->comment;
            }

            $rows[] = $row;
        }

        return $rows;
    }

    private function get_coursework_students() : array
    {
        global $DB, $USER;

        $conditions = array('coursework'=>$this->cm->instance, 'tutor'=>$USER->id);
        $cwStudents = $DB->get_records('coursework_students', $conditions);

        $students = array();
        foreach($cwStudents as $value)
        {
            $students[] = $value->student;
        }

        $students = cw_add_user_names($students);
        usort($students, "cw_cmp_users");

        return $students;
    }

    // Gui functions
    protected function get_interface_html_form() : string
    {
        $str = '';
        for($i = 0; $i < count($this->students); $i++)
        {
            $str.= '<form id="'.TUTOR_FORM.$i.'">';
            $str.= '<input type="hidden" name="id" value="'.$this->cm->id.'" >';
            $str.= '<input type="hidden" name="'.ECM_STUDENTS.'" value="'.$this->students[$i]->student.'" >'; // !!!!!!!!!
            $str.= '</form>';
        }
        return $str;
    }

    protected function get_student_name($row, $i) : string
    {
        $str = '<td>';
        $str.= cw_get_user_photo($row->student);
        $str.= ' '.cw_get_user_name($row->student);
        $str.= '</td>';
        return $str;
    }

    protected function get_student_group($row, $i) : string
    {
        return '<td>'.$row->group.'</td>';
    }

    protected function get_leader_cell($row, $i) : string
    {
        $str = '<td>';
        if(isset($row->leader)) $str .= cw_get_user_name($row->leader);
        $str.= '</td>';
        return $str;
    }

    protected function get_course_cell($row, $i) : string
    {
        $str = '<td>';
        if(!empty($row->course)) $str .= cw_get_course_name($row->course);
        else $str.= get_string('not_selected', 'coursework');
        $str.= '</td>';
        return $str;
    }

    protected function get_theme_cell($row, $i) : string
    {
        $str = '<td>';
        if(isset($row->theme) && $row->theme) $str.= cw_get_theme_name($row->theme);
        else if(isset($row->owntheme) && $row->owntheme) $str .= $row->owntheme;
        else $str.= get_string('not_selected', 'coursework');
        $str.= '</td>';
        return $str;
    }

    protected function get_grade_cell($row, $i) : string
    {
        $str = '<td><center>';
        $str.= '<input type="number" form="'.TUTOR_FORM.$i.'" ';
        $str.= 'name="'.ECM_GRADE.'" ';

        if(isset($row->grade)) $str .= ' value="'.$row->grade.'" ';

        $str.= ' style="width: 40px;" >';
        $str.= '</center></td>';

        return $str;
    }

    protected function get_comment_cell($row, $i) : string
    {
        $str = '<td><center>';
        $str.= '<textarea form="'.TUTOR_FORM.$i.'" name="'.ECM_COMMENT.'" >';

        if(isset($row->comment)) $str .= $row->comment;

        $str.= '</textarea></center></td>';

        return $str;
    }

    protected function get_btn_cell($row, $i) : string
    {
        $str = '<td class="transparent">';

        $str.= '<input type="hidden" form="'.TUTOR_FORM.$i.'" ';
        $str.= 'name="'.ECM_GRADE_STUDENT.'" value="'.ECM_GRADE_STUDENT.'" >';

        $str.= '<button  form="'.TUTOR_FORM.$i.'" >';
        $str.= get_string('grade_student', 'coursework');
        $str.= '</button></td>';

        return $str;
    }

}

