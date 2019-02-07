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
        $tableRows = array();

        foreach($students as $student)
        {
            $tableRow = new stdClass();
            $tableRow->student = $student->id;
            $tableRow->group = cw_get_user_groups_names($this->course->id, $student->id);

            $coursework = cw_get_coursework_student($this->cm->instance, $student->id);

            if(isset($coursework) && isset($coursework->id))
            {
                $tableRow->id = $coursework->id;
                $tableRow->tutor = $coursework->tutor;
                $tableRow->course = $coursework->course;
                $tableRow->theme = $coursework->theme;
                $tableRow->owntheme = $coursework->owntheme;
                $tableRow->grade = $coursework->grade;
                $tableRow->comment = $coursework->comment;
            }

            $tableRows[] = $tableRow;
        }

        return $tableRows;
    }

    private function get_coursework_students() : array 
    {
        $students = array();
        $studentArchetypeRoles = cw_get_archetype_roles(array('student'));
        $students = cw_get_coursework_users_with_archetype_roles($studentArchetypeRoles, $this->course->id, $this->cm->instance);
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
            $str.= '<input type="hidden" name="'.RECORD.ID.'" value="'.$this->students[$i]->id.'" >';
            $str.= '<input type="hidden" name="'.DB_EVENT.'" value="'.DEL.STUDENT.'" >';
            $str.= '<button onclick=" return confirm_remove_selection()">'.get_string('remove_selection', 'coursework').'</button>';
            $str.= '</form>';
        }
        $str.= '</td>';

        return $str;
    }

}

