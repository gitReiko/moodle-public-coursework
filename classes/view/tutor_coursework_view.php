<?php

class TutorCourseworkView extends CourseworkView
{

    // Database functions
    protected function database_events_handler() : void
    {
        $recordId = optional_param(RECORD.ID, 0, PARAM_INT);

        if($recordId) $this->update_coursework_students($recordId);
    }

    private function update_coursework_students(int $recordId) : void
    {
        global $DB;

        $grade = optional_param(GRADE, 0, PARAM_INT);
        $comment = optional_param(COMMENT, 0, PARAM_TEXT);

        $sql = 'UPDATE {coursework_students} SET grade = ?, comment = ? WHERE id = ?';
        $params = array($grade, $comment, $recordId);
        
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
        $message->userto = optional_param(STUDENT.ID, 0, PARAM_INT);
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
        $params = cw_prepare_data_for_message();
        $message = get_string('tutor_message','coursework', $params);
        $notification = get_string('grade_isnt_final', 'coursework');
        $notification.= get_string('answer_not_require', 'coursework');

        return cw_get_html_message($this->cm, $this->course->id, $message, $notification);
    }

    // Constructor functions
    protected function get_coursework_students_database_records() : array
    {
        global $USER;

        $students = $this->get_coursework_students();
        $rows = array();

        foreach($students as $student)
        {
            $tableRow = new stdClass();
            $tableRow->student = $student->id;
            $tableRow->group = cw_get_user_groups_names($this->course->id, $student->id);

            $coursework = cw_get_coursework_student($this->cm->instance, $student->id);

            if(isset($coursework) && isset($coursework->id))
            {
                $tableRow->dbRecordId = $coursework->id;
                $tableRow->tutor = $coursework->tutor;
                $tableRow->course = $coursework->course;
                $tableRow->grade = $coursework->grade;
                $tableRow->theme = $coursework->theme;
                $tableRow->owntheme = $coursework->owntheme;
                $tableRow->comment = $coursework->comment;
            }

            $rows[] = $tableRow;
        }

        return $rows;
    }

    private function get_coursework_students() : array
    {
        global $DB, $USER;

        global $DB;
        $sql = 'SELECT cs.student AS id, u.firstname, u.lastname
                FROM {coursework_students} as cs, {user} as u
                WHERE cs.student = u.id AND u.suspended = 0 
                AND cs.coursework = ? AND cs.tutor = ?
                ORDER BY u.lastname';
        $conditions = array($this->cm->instance, $USER->id);
        $students = array();
        $students = $DB->get_records_sql($sql, $conditions);
        $students = cw_add_fullnames_to_users_array($students);
        return $students;
    }

    // Gui functions
    protected function get_interface_html_form() : string
    {
        $str = '';
        for($i = 0; $i < count($this->tableRows); $i++)
        {
            $str.= '<form id="'.TUTOR_FORM.$i.'">';
            $str.= '<input type="hidden" name="id" value="'.$this->cm->id.'" >';
            $str.= '<input type="hidden" name="'.STUDENT.ID.'" value="'.$this->tableRows[$i]->student.'" >';
            $str.= '<input type="hidden" name="'.RECORD.ID.'" value="'.$this->tableRows[$i]->dbRecordId.'" >';
            $str.= '</form>';
        }
        return $str;
    }

    protected function get_grade_cell($tableRow, $i) : string
    {
        $str = '<td><center>';
        $str.= '<input type="number" form="'.TUTOR_FORM.$i.'" ';
        $str.= 'name="'.GRADE.'" ';

        if(isset($tableRow->grade)) $str .= ' value="'.$tableRow->grade.'" ';

        $str.= ' style="width: 40px;" >';
        $str.= '</center></td>';

        return $str;
    }

    protected function get_comment_cell($tableRow, $i) : string
    {
        $str = '<td><center>';
        $str.= '<textarea form="'.TUTOR_FORM.$i.'" name="'.COMMENT.'" >';

        if(isset($tableRow->comment)) $str .= $tableRow->comment;

        $str.= '</textarea></center></td>';

        return $str;
    }

    protected function get_btn_cell($tableRow, $i) : string
    {
        $str = '<td class="transparent">';
        $str.= '<button  form="'.TUTOR_FORM.$i.'" >';
        $str.= get_string('grade_student', 'coursework');
        $str.= '</button></td>';

        return $str;
    }

}

