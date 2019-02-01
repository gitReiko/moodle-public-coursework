<?php


class StudentsAssignment
{
    private $course;
    private $cm;

    private $studentsGroups;
    private $tutors;

    public function execute() : string
    {
        $str = '';
        $databaseEventHandler = new StudentsAssignmentDatabaseEventsHandler($this->course, $this->cm);
        $str.= $databaseEventHandler->execute();
        $str.= $this->get_gui();
        return $str;
    }

    // Constructor functions
    function __construct($course, $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->studentsGroups = $this->get_students_groups();
        $this->tutors = $this->get_unique_tutors();
    }

    private function get_students_groups() : array
    {
        global $DB;
        $sql = 'SELECT cg.groupid AS id, g.name
                FROM {coursework_groups} AS cg, {groups} AS g
                WHERE cg.groupid = g.id AND cg.coursework = ?
                ORDER BY g.name';
        $conditions = array($this->cm->instance);
        $studentsGroups = array();
        $studentsGroups = $DB->get_records_sql($sql, $conditions);
        $studentsGroups = $this->add_count_of_students_to_groups_array($studentsGroups);
        return $studentsGroups;
    }

    private function add_count_of_students_to_groups_array(array $groups) : array
    {
        foreach($groups as $group)
        {
            $group->studentsCount = $this->get_count_of_students_in_group($group->id);
        }
        return $groups;
    }

    private function get_count_of_students_in_group(int $groupID) : int
    {
        global $DB;
        $studentsCount = 0;
        $members = $DB->get_records('groups_members', array('groupid'=>$groupID));
        foreach($members as $member)
        {
            if(cw_is_user_have_student_role_in_course($member->userid, $this->course->id))
            {
                $studentsCount++;
            }
        }
        return $studentsCount;
    }

    private function get_unique_tutors() : array
    {
        global $DB;
        $sql = 'SELECT DISTINCT ct.tutor AS id, u.firstname, u.lastname
                FROM {coursework_tutors} AS ct, {user} AS u
                WHERE ct.tutor = u.id AND ct.coursework = ?
                ORDER BY u.lastname';
        $conditions = array($this->cm->instance);
        $tutors = array();
        $tutors = $DB->get_records_sql($sql, $conditions);
        if($this->is_users_isset($tutors))
        {
            $this->edit_user_names($tutors);
            $tutors = $this->add_total_students_quotas_in_tutors_array($tutors);
        }

        return $tutors;
    }

    private function add_total_students_quotas_in_tutors_array(array $tutors) : array
    {
        global $DB;

        foreach ($tutors as $tutor)
        {
            $totalQuota = 0;

            $conditions = array('coursework'=>$this->cm->instance, 'tutor'=>$tutor->id);
            $tutorsRecords = $DB->get_records('coursework_tutors', $conditions);

            foreach ($tutorsRecords as $tutorRecord)
            {
                $totalQuota += $tutorRecord->quota;
            }

            $tutor->totalQuota = $totalQuota;
        }

        return $tutors;
    }

    private function is_users_isset(array $users) : bool
    {
        if(isset(reset($users)->lastname)) return true;
        else return false;
    }

    private function edit_user_names(array $users) : array
    {
        foreach($users as $user)
        {
            $user->fullname = $user->lastname.' ';

            $firstname = mb_split(' ', $user->firstname);
            foreach($firstname as $initial)
            {
                $user->fullname .= mb_substr($initial, 0, 1).'.';
            }
        }
        return $users;
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
        $str.= '<select form="'.TUTOR_FORM.'" name="'.GROUP.ASSIGNMENT.TUTOR.ID.'[]"';
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
        $str.= '<input type="hidden" name="'.ECM_MODULE.'" value="'.STUDENTS_ASSIGNMENT.'">';
        return $str;
    }

}

class StudentsAssignmentDatabaseEventsHandler
{
    private $course;
    private $cm;

    private $groups;
    private $tutors;

    public function execute() : void
    {
        if($this->is_group_assignment_event_has_been_sent())
        {
            $this->add_to_database_group_assignment();
        }



    }

    function __construct($course, $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->groups = optional_param_array(GROUP.ASSIGNMENT.GROUP.ID, array(), PARAM_INT);
        $this->tutors = optional_param_array(GROUP.ASSIGNMENT.TUTOR.ID, array(), PARAM_INT);
    }

    private function is_group_assignment_event_has_been_sent() : bool
    {
        if(count($this->groups)) return true;
        else return false;
    }

    private function add_to_database_group_assignment() : void
    {
        for($i = 0; $i < count($this->groups); $i++)
        {
            $students = $this->get_students_from_group($this->groups[$i]);

            foreach($students as $student)
            {
                $assignment = $this->get_personal_assignment($student, $this->tutors[$i]);

                if($this->is_conditions_for_adding_assignment_are_met($this->tutors[$i], $assignment))
                {
                    $this->add_assignment_to_database($assignment);
                }
            }

        }
    }

    private function get_students_from_group(int $groupid) : array
    {
        $members = $this->get_group_members($groupid);
        $students = array();
        foreach($members as $member)
        {
            if($this->is_user_have_role_student($member)) $students[] = $member;
        }
        return $students;
    }

    private function get_group_members(int $groupid) : array
    {
        global $DB;
        $members = $DB->get_records('groups_members', array('groupid'=>$groupid));

        $temp = array();
        foreach($members as $member)
        {
            $temp[] = $member->userid;
        }
        return $temp;
    }

    private function is_user_have_role_student(int $userid) : bool
    {
        $roles = get_user_roles(context_course::instance($this->course->id), $userid);
        foreach($roles as $role)
        {
            if($role->roleid == STUDENT_ROLE) return true;
        }
        return false;
    }

    private function get_personal_assignment(int $student, int $tutor) : stdClass
    {
        $assignment = new stdClass;
        $assignment->coursework = $this->cm->instance;
        $assignment->student = $student;
        $assignment->tutor = $tutor;
        return $assignment;
    }

    private function is_conditions_for_adding_assignment_are_met(int $tutorid, stdClass $assignment) : bool
    {
        try
        {
            if($this->is_tutor_appointed($tutorid))
            {
                if($this->is_student_didnt_choose_theme($assignment))
                {
                    if($this->is_tutor_have_enough_quota($assignment)) return true;
                    else $this->throw_not_enough_quota_exception($assignment);
                }
                else throw new Exception(get_string('error_student_already_chosen_theme', 'coursework', cw_get_user_name($assignment->student)));
            }
            return false;
        }
        catch(Exception $e)
        {
            cw_print_error_message($e->getMessage());
            return false;
        }
    }

    private function is_tutor_appointed(int $tutorid) : bool
    {
        if($tutorid) return true;
        else return false;
    }

    private function is_student_didnt_choose_theme(stdClass $assignment) : bool
    {
        global $DB;
        $conditions = array('coursework' => $assignment->coursework, 'student' => $assignment->student);
        if($DB->record_exists('coursework_students', $conditions)) return false;
        else return true;
    }

    private function is_tutor_have_enough_quota(stdClass $assignment) : bool
    {
        $totalQuota = $this->get_total_tutor_quota($assignment);
        $usedQuota = $this->get_used_tutor_quota($assignment);

        if(($totalQuota - $usedQuota) > 0) return true;
        else return false;
    }

    private function get_total_tutor_quota(stdClass $assignment) : int
    {
        global $DB;
        $conditions = array('coursework'=>$assignment->coursework, 'tutor' => $assignment->tutor);
        $tutorsRecords = $DB->get_records('coursework_tutors', $conditions);

        $totalQuota = 0;
        foreach($tutorsRecords as $tutorRecord)
        {
            $totalQuota += $tutorRecord->quota;
        }
        return $totalQuota;
    }

    private function get_used_tutor_quota(stdClass $assignment) : int
    {
        global $DB;
        $conditions = array('coursework'=>$assignment->coursework, 'tutor' => $assignment->tutor);
        return $DB->count_records('coursework_students', $conditions);
    }

    private function add_assignment_to_database(stdClass $assignment) : void
    {
        global $DB;
        $DB->insert_record('coursework_students', $assignment, false);
    }

    private function throw_not_enough_quota_exception(stdClass $assignment) : void
    {
        $params = new stdClass;
        $params->tutor = cw_get_user_name($assignment->tutor);
        $params->student = cw_get_user_name($assignment->student);

        $message = get_string('error_tutor_total_quota_over', 'coursework', $params);

        throw new Exception($message);
    }

}
