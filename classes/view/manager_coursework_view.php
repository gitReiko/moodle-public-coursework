<?php

class ManagerCourseworkView extends CourseworkView
{

    // Database functions
    protected function database_events_handler() : void
    {
        $delete = optional_param(ECM_REMOVE_SELECTION, 0, PARAM_TEXT);
        $student = optional_param(ECM_STUDENTS, 0, PARAM_INT);

        if($delete)
        {
            if($student)
            {
                $courseworkid = cw_get_coursework_students_id($this->cm->instance, $student);

                if($delete && $courseworkid)
                {
                    global $DB;
                    if($DB->delete_records('coursework_students', array('id'=>$courseworkid)))
                    {
                        $this->send_message($student);
                    }
                }
            }
            else
            {
                echo get_string('error_no_student', 'coursework');
            }
        }
    }

    // Message functions
    private function send_message($student) : void
    {
        global $CFG, $USER;

        $message = new \core\message\message();
        $message->component = 'mod_coursework';
        $message->name = 'selectionremoved';
        $message->userfrom = $USER;
        $message->userto = $student;
        $message->subject = get_string('selectionremoved:head','coursework');
        $message->fullmessage = get_string('selectionremoved:body','coursework');
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml = $this->get_html_message();
        $message->smallmessage = get_string('selectionremoved:head','coursework');
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
        $message.= get_string('selectionremoved1','coursework');
        $message.= '</p>';
        $message.='<p>'.get_string('selectionremoved2','coursework').'</p>';

        $notifications = '<p>'.get_string('answer_not_require', 'coursework').'</p>';

        return cw_get_html_message($this->cm, $this->course->id, $message, $notifications);
    }

    // Constructor functions
    protected function get_coursework_students_database_records() : array
    {
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
                $row->theme = $coursework->theme;
                $row->owntheme = $coursework->owntheme;
                $row->grade = $coursework->grade;
                $row->comment = $coursework->comment;
            }

            $rows[] = $row;
        }

        return $rows;
    }

    private function get_coursework_students() : array
    {
        global $DB, $PAGE;
        $groups = $DB->get_records('coursework_groups', array('coursework'=>$this->cm->instance));
        $students = array();

        foreach($groups as $group)
        {
            $members = $DB->get_records('groups_members', array('groupid'=>$group->groupid),'','userid');

            foreach($members as $member)
            {
                $roles = get_user_roles(context_course::instance($this->course->id), $member->userid);

                foreach($roles as $role)
                {
                    if($role->roleid == STUDENT_ROLE)
                    {
                        $students[] = $member->userid;
                    }
                }
            }
        }

        $students = array_unique($students);
        $students = cw_add_user_names($students);
        usort($students, "cw_cmp_users");

        return $students;
    }

    // Gui functions
    protected function get_interface_html_form() : string
    {
        return '';
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
        if(isset($row->leader))
        {
            $str.= cw_get_user_photo($row->leader);
            $str.= ' '.cw_get_user_name($row->leader);
        }
        else $str .= get_string('not_selected', 'coursework');
        $str.= '</td>';
        return $str;
    }

    protected function get_course_cell($row, $i) : string
    {
        $str = '<td>';
        if(!empty($row->course)) $str .= cw_get_course_name($row->course);
        else $str .= get_string('not_selected', 'coursework');
        $str.= '</td>';
        return $str;
    }

    protected function get_theme_cell($row, $i) : string
    {
        $str = '<td>';
        if(!empty($row->theme) && $row->theme) $str.= cw_get_theme_name($row->theme);
        else if(isset($row->owntheme) && $row->owntheme) $str .= $row->owntheme;
        else $str.= get_string('not_selected', 'coursework');
        $str.= '</td>';
        return $str;
    }

    protected function get_grade_cell($row, $i) : string
    {
        $str = '<td>';
        if(isset($row->grade) && $row->grade) $str .= $row->grade;
        $str.= '</td>';
        return $str;
    }

    protected function get_comment_cell($row, $i) : string
    {
        $str = '<td>';
        if(isset($row->comment)) $str .= $row->comment;
        $str.= '</td>';
        return $str;
    }

    protected function get_btn_cell($row, $i) : string
    {
        $str = '<td class="transparent">';
        if(isset($row->leader))
        {
            $str.= '<form>';
            $str.= '<input type="hidden" name="id" value="'.$this->cm->id.'" >';
            $str.= '<input type="hidden" name="'.ECM_STUDENTS.'" value="'.$this->students[$i]->student.'" >';
            $str.= '<input type="hidden" name="'.ECM_REMOVE_SELECTION.'" value="'.ECM_REMOVE_SELECTION.'" >';
            $str.= '<button onclick=" return confirm_remove_selection()">'.get_string('remove_selection', 'coursework').'</button>';
            $str.= '</form>';
        }
        $str.= '</td>';

        return $str;
    }

}

