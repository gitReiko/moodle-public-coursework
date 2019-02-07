<?php

require_once 'manager_view_database_event_handler.php';

class ManagerCourseworkView extends CourseworkView
{

    // Database functions
    protected function database_events_handler() : void
    {
        $handler = new ManagerViewDatabaseEventHandler($this->course, $this->cm);
        $handler->execute();
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

            $coursework = cw_get_coursework_student($this->cm->instance, $student->id);

            if(isset($coursework) && isset($coursework->id))
            {
                $row->dbRecordId = $coursework->id;
                $row->tutor = $coursework->tutor;
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

    protected function get_btn_cell($row, $i) : string
    {
        $str = '<td class="transparent">';
        if(isset($row->tutor))
        {
            $str.= '<form>';
            $str.= '<input type="hidden" name="id" value="'.$this->cm->id.'" >';
            $str.= '<input type="hidden" name="'.RECORD.ID.'" value="'.$this->students[$i]->dbRecordId.'" >';
            $str.= '<input type="hidden" name="'.DB_EVENT.'" value="'.DEL.STUDENT.'" >';
            $str.= '<button onclick=" return confirm_remove_selection()">'.get_string('remove_selection', 'coursework').'</button>';
            $str.= '</form>';
        }
        $str.= '</td>';

        return $str;
    }

}

