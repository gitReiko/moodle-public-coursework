<?php


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
                $assignment = $this->get_personal_assignment($student->id, $this->tutors[$i]);

                if($this->is_conditions_for_adding_assignment_are_met($this->tutors[$i], $assignment))
                {
                    $this->add_assignment_to_database($assignment);
                }
            }

        }
    }

    private function get_students_from_group(int $groupid) : array
    {
        $students = array();
        $studentsRoles = cw_get_archetype_roles(array('student'));
        $members = cw_get_group_members($groupid);
        foreach($members as $member)
        {
            if($this->is_user_have_role_student($studentsRoles, $member->id)) $students[] = $member;
        }
        return $students;
    }

    private function is_user_have_role_student(array $studentRoles, int $memberid) : bool
    {
        $memberRoles = get_user_roles(context_course::instance($this->course->id), $memberid);

        if(cw_is_user_archetype($memberRoles, $studentRoles)) return true;
        else return false;
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
                else throw new Exception(get_string('e:student-already-chosen-theme', 'coursework', cw_get_user_name($assignment->student)));
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

        $message = get_string('e:tutor-total-quota-over', 'coursework', $params);

        throw new Exception($message);
    }

}


