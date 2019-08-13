<?php

class TeacherCourseworkView extends CourseworkView
{

    // Constructor functions
    protected function checkExceptions() : void { }

    protected function get_coursework_students_database_records() : array
    {
        global $USER;

        $students = $this->get_coursework_students();
        $rows = array();

        if(!count($students)) throw new Exception(get_string('no_one_has_chosen_you_as_leader', 'coursework'));

        foreach($students as $student)
        {
            $tableRow = new stdClass();
            $tableRow->student = $student->id;
            $tableRow->group = cw_get_user_groups_names($this->course->id, $student->id);

            $coursework = cw_get_coursework_student($this->cm->instance, $student->id);

            if(isset($coursework) && isset($coursework->id))
            {
                $tableRow->dbRecordId = $coursework->id;
                $tableRow->teacher = $coursework->teacher;
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
                AND cs.coursework = ? AND cs.teacher = ?
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
            $str.= '<form id="'.TEACHER_FORM.$i.'">';
            $str.= '<input type="hidden" name="id" value="'.$this->cm->id.'" >';
            $str.= '<input type="hidden" name="'.STUDENT.ID.'" value="'.$this->tableRows[$i]->student.'" >';
            $str.= '<input type="hidden" name="'.RECORD.ID.'" value="'.$this->tableRows[$i]->dbRecordId.'" >';
            $str.= '<input type="hidden" name="'.DB_EVENT.'" value="'.UPDATE.STUDENT.'" >';
            $str.= '</form>';
        }
        return $str;
    }

    protected function get_grade_cell($tableRow, $i) : string
    {
        $str = '<td><center>';
        $str.= '<input type="number" form="'.TEACHER_FORM.$i.'" ';
        $str.= 'name="'.GRADE.'" ';

        if(isset($tableRow->grade)) $str .= ' value="'.$tableRow->grade.'" ';

        $str.= ' style="width: 40px;" min="0" max="10">';
        $str.= '</center></td>';

        return $str;
    }

    protected function get_comment_cell($tableRow, $i) : string
    {
        $str = '<td><center>';
        $str.= '<textarea form="'.TEACHER_FORM.$i.'" name="'.COMMENT.'" >';

        if(isset($tableRow->comment)) $str .= $tableRow->comment;

        $str.= '</textarea></center></td>';

        return $str;
    }

    protected function get_btn_cell($tableRow, $i) : string
    {
        $str = '<td class="transparent">';
        $str.= '<button  form="'.TEACHER_FORM.$i.'" >';
        $str.= get_string('grade_student', 'coursework');
        $str.= '</button></td>';

        return $str;
    }

}

