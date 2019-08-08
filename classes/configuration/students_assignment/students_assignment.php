<?php

require_once 'students_assignment_database_events_handler.php';

class StudentsAssignment
{
    private $course;
    private $cm;

    private $studentsGroups;
    private $tutors;

    public function execute() : string
    {
        return $this->get_gui();
    }

    // Constructor functions
    function __construct($course, $cm)
    {
        // Init necessary for database processing params
        $this->course = $course;
        $this->cm = $cm;

        // Process database events
        $this->handle_database_events();

        // Init other params
        $this->studentsGroups = groups_get_activity_allowed_groups($this->cm);
        $this->tutors = $this->get_unique_tutors();
    }

    private function handle_database_events() : void 
    {
        $event = optional_param(DB_EVENT, 0 , PARAM_TEXT);
        
        if($event)
        {
            $databaseEventHandler = new StudentsAssignmentDatabaseEventsHandler($this->course, $this->cm);
            $databaseEventHandler->execute(); 
        }
    }

    private function get_unique_tutors() : array
    {
        global $DB;
        $sql = 'SELECT DISTINCT ct.teacher AS id, u.firstname, u.lastname
                FROM {coursework_teachers} AS ct, {user} AS u
                WHERE ct.teacher = u.id AND u.suspended = 0 AND ct.coursework = ?
                ORDER BY u.lastname';
        $conditions = array($this->cm->instance);
        $tutors = array();
        $tutors = $DB->get_records_sql($sql, $conditions);

        if(isset($tutors))
        {
            $tutors = cw_add_fullnames_to_users_array($tutors);
            $tutors = $this->add_total_students_quotas_in_tutors_array($tutors);
        }

        return $tutors;
    }

    private function add_total_students_quotas_in_tutors_array(array $tutors) : array
    {
        global $DB;

        foreach($tutors as $tutor)
        {
            $totalQuota = 0;

            $conditions = array('coursework'=>$this->cm->instance, 'tutor'=>$tutor->id);
            $tutorRecords = $DB->get_records('coursework_teachers', $conditions);

            foreach ($tutorRecords as $tutorRecord)
            {
                $totalQuota += $tutorRecord->quota;
            }

            $tutor->totalQuota = $totalQuota;
        }

        return $tutors;
    }

    private function get_gui() : string
    {
        $str = '';
        $str.= $this->get_module_header();
        $str.= $this->get_assignment_table();
        $str.= $this->get_student_assignment_form();
        return $str;
    }

    private function get_module_header() : string
    {
        return '<h3>'.get_string('students_assignment_header', 'coursework').'</h3>';
    }

    private function get_assignment_table() : string
    {
        $str = '';
        $str.= $this->get_assignment_table_header();
        $str.= $this->get_assignment_table_body();
        return $str;
    }

    private function get_assignment_table_header() : string
    {
        $str = '<table class="students_assignment"><tr class="header">';
        $str.= '<td colspan="2">'.get_string('group_assignment', 'coursework').'</td>';
        $str.= '</tr>';
        return $str;
    }

    private function get_assignment_table_body() : string
    {
        $str = '';
        foreach($this->studentsGroups as $group)
        {
            $str.= '<tr>';
            $str.= $this->get_groups_table_cell($group);
            $str.= $this->get_tutors_table_cell($group);
            $str.= '</tr>';
        }
        $str.= '</table>';
        return $str;
    }

    private function get_groups_table_cell(stdClass $group) : string
    {
        $str = '<td>';
        $str.= $group->name;
        $str.= '<input type="hidden" form="'.TUTOR_FORM.'" name="'.GROUP.ASSIGNMENT.GROUP.ID.'[]" value="'.$group->id.'">';
        $str.= '</td>';
        return $str;
    }

    private function get_tutors_table_cell(stdClass $group) : string
    {
        $str = '<td>';
        $str.= '<select form="'.TUTOR_FORM.'" name="'.GROUP.ASSIGNMENT.TEACHER.ID.'[]"';
        $str.= ' onchange="check_tutor_quota_sufficiency(this)" autocomplete="off" >';
        $str.= $this->get_no_assign_option();
        foreach($this->tutors as $tutor)
        {
            $str.= '<option value="'.$tutor->id.'" ';
            $str.= $this->get_attributes_of_tutor_quota_and_count_of_students_in_group($group, $tutor);
            $str.= '>'.$tutor->fullname.'</option>';
        }
        $str.='</select>';
        $str.= '</td>';
        return $str;
    }

    private function get_attributes_of_tutor_quota_and_count_of_students_in_group(stdClass $group, stdClass $tutor) :string
    {
        return ' data-tutor-quota="'.$tutor->totalQuota.'" data-count-of-students-in-group="'.$group->studentsCount. ' " ';
    }

    private function get_no_assign_option() : string
    {
        return '<option value="0">'.get_string(NO_ASSIGN, 'coursework').'</option>';
    }

    private function get_student_assignment_form() : string
    {
        $str = '<form id="'.TUTOR_FORM.'">';
        $str.= $this->get_save_button();
        $str.= $this->get_necessary_form_inputs();
        $str.= '</form>';
        return $str;
    }

    private function get_save_button() : string
    {
        return '<br><button>'.get_string('save_changes', 'coursework').'</button>';
    }

    private function get_necessary_form_inputs() : string
    {
        $str = '<input type="hidden" name="'.ID.'" value="'.$this->cm->id.'">';
        $str.= '<input type="hidden" name="'.CONFIG_MODULE.'" value="'.STUDENTS_DISTRIBUTION.'">';
        $str.= '<input type="hidden" name="'.DB_EVENT.'" value="'.DB_EVENT.'" >';
        return $str;
    }

}

