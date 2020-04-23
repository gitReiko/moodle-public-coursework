<?php 

namespace coursework_lib
{
    // Database functions
    function get_user_course_groups(int $courseid, int $userid)
    {
        global $DB;
        $conditions = array($courseid, $userid);
        $sql = 'SELECT g.id, g.name
                FROM {groups} as g, {groups_members} AS gm
                WHERE g.id = gm.groupid AND g.courseid = ? AND gm.userid = ?';

        return $DB->get_records_sql($sql, $conditions);
    }

    function get_coursework_teachers(int $courseworkId)
    {
        global $DB;
        $sql = 'SELECT ct.id as recordid, ct.teacher as teacherid, u.firstname, u.lastname, 
                    ct.course as courseid, c.fullname as coursename, ct.quota
                FROM {coursework_teachers} as ct, {user} as u, {course} as c
                WHERE ct.teacher = u.id AND ct.course = c.id AND coursework = ?';
        $conditions = array($courseworkId);

        $teachers = $DB->get_records_sql($sql, $conditions);
        $teachers = cw_add_fullnames_to_users_array($teachers);

        return $teachers;
    }

    function get_distribute_students() : array 
    {
        $students = array();
        $strings = optional_param_array(STUDENT, null, PARAM_TEXT);

        foreach($strings as $string) 
        {
            $str = explode(SEPARATOR, $string);

            $student = new \stdClass;
            $student->id = $str[0];
            $student->fullname = $str[1];

            $students[] = $student;
        }

        return $students;
    }

    function get_course_fullname(int $courseid) : string 
    {
        global $DB;
        $course = $DB->get_record('course', array('id'=>$courseid));
        return $course->fullname;
    }

    function get_remaining_leader_quota(int $courseworkid, int $teacherid, int $courseid) : int 
    {
        $allLeaderQuota = get_leader_quota($courseworkid, $teacherid, $courseid);
        $usedQuota = get_used_quota($courseworkid, $teacherid, $courseid);

        return $allLeaderQuota - $usedQuota;
    }

    function get_leader_quota(int $courseworkid, int $teacherid, int $courseid) : int 
    {
        global $DB;
        $conditions = array('coursework'=>$courseworkid, 'teacher'=>$teacherid, 'course'=>$courseid);

        $leader = $DB->get_record('coursework_teachers', $conditions);

        if(empty($leader->quota)) throw new Exception(get_string('e-sd-ev:missing_leader_quota', 'coursework'));
        if(!is_numeric($leader->quota)) throw new Exception(get_string('e-sd-ev:quota_isnt_numeric', 'coursework'));

        return (int)$leader->quota;
    }

    function get_used_quota(int $courseworkid, int $teacherid, int $courseid) : int 
    {
        global $DB;
        $conditions = array('coursework'=>$courseworkid, 'teacher'=>$teacherid, 'course'=>$courseid);

        return $DB->count_records('coursework_students', $conditions);
    }

    // Other functions
    function get_green_message(string $message) : string 
    {
        return '<span style="background-color: #b4eeb4;">'.$message.'</span><br>';
    }

    function get_red_message(string $message) : string 
    {
        return '<span style="background-color: #fa8072;">'.$message.'</span><br>';
    }

    function is_user_manager(\stdClass $cm, int $userId) : bool 
    {
        if(has_capability('mod/coursework:is_manager', \context_module::instance($cm->id), $userId))
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    function is_user_teacher(\stdClass $cm, int $userId) : bool 
    {
        // Managers can also be teachers.
        if(has_capability('mod/coursework:is_teacher', \context_module::instance($cm->id), $userId)
            && !has_capability('mod/coursework:is_manager', \context_module::instance($cm->id), $userId))
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    function is_user_student(\stdClass $cm, int $userId) : bool 
    {
        // Managers and teachers cannot be students.
        if(has_capability('mod/coursework:is_student', \context_module::instance($cm->id), $userId)
            && !has_capability('mod/coursework:is_teacher', \context_module::instance($cm->id), $userId))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    function get_coursework_students(\stdClass $cm)
    {
        $students = array();
        $groups = groups_get_activity_allowed_groups($cm);
        foreach($groups as $group)
        {
            $members = groups_get_members($group->id, 'u.id,u.firstname,u.lastname', 'u.lastname');

            foreach($members as $member)
            {
                if(is_user_student($cm, $member->id))
                {
                    $students[] = $member;
                }
            }
        }

        $students = cw_array_unique_for_stdclass($students);
        $students = cw_add_fullnames_to_users_array($students);
        usort($students, "compare_user_fullnames");

        return $students;
    }

    function get_coursework_students_for_in_query(\stdClass $cm) : string
    {
        $inQuery = '';
        $students = get_coursework_students($cm);
        foreach($students as $student)
        {
            $inQuery.= $student->id.',';
        }
        $inQuery = mb_substr($inQuery, 0, -1);
        return $inQuery;
    }

    function get_coursework_students_with_groups_leaders_courses(\stdClass $cm, array $allowedActivityGroups)
    {
        $students = get_coursework_students($cm);
        $student = add_groups_to_students_array($students, $allowedActivityGroups);
        $student = add_leaders_and_courses_to_students_array($cm, $students);

        return $students;
    }

    function add_groups_to_students_array(array $students, array $allowedActivityGroups) : array 
    {
        foreach($students as $student)
        {
            foreach($allowedActivityGroups as $group)
            {
                if(groups_is_member($group->id, $student->id))
                {
                    $temp = new \stdClass;
                    $temp->id = $group->id;
                    $temp->name = $group->name;

                    $student->groups[] = $temp;
                }
            }
        }

        return $students;
    }

    function add_leaders_and_courses_to_students_array(\stdClass $cm, array $students) : array 
    {
        foreach($students as $student)
        {
            if(empty($student->leader))
            {
                $temp = get_student_leader_and_course($cm, $student);

                if(isset($temp->teacher))
                {
                    $student->leader = cw_get_user_name($temp->teacher);
                    $student->course = get_course_fullname($temp->course);
                }
                else
                {
                    $student->leader = '';
                    $student->course = '';                 
                }
            }
        }

        return $students;
    }

    function get_student_leader_and_course(\stdClass $cm, \stdClass $student) 
    {
        global $DB;
        $conditions = array('coursework'=>$cm->instance, 'student'=>$student->id);
        return $DB->get_record('coursework_students', $conditions);
    }

    function get_all_course_teachers(\stdClass $cm) : array 
    {
        // This method returns list of users with given capability, it ignores enrolment status and should be used only above the course contex.
        $teachers = get_users_by_capability(\context_module::instance($cm->id), 'mod/coursework:is_teacher', 'u.id,u.firstname,u.lastname', 'u.lastname');
        $teachers = cw_add_fullnames_to_users_array($teachers);
        return $teachers;
    }

    function get_user_record(int $userID) : \stdClass
    {
        try
        {
            global $DB;
            $user = $DB->get_record('user', array('id'=>$userID));

            if(empty($user->id)) throw new Exception(get_string('e:missing-user-record', 'coursework'));

            return $user;
        }
        catch(Exception $e)
        {
            cw_print_error_message($e->getMessage());
        }
    }

    function get_all_site_courses() : array
    {
        global $DB;
        $courses = array();
        $courses = $DB->get_records('course', array(), 'fullname', 'id, fullname');
        return $courses;
    }

    function get_user_stdclass(int $id) : \stdClass
    {
        global $DB;
        $user = $DB->get_record('user', array('id'=>$id), 'id, firstname, lastname');
        $user->fullname = get_user_fullname($user);
        return $user;
    }

    function get_user_from_id(int $id) : \stdClass 
    {
        global $DB;
        return $DB->get_record('user', array('id'=>$id), 'id, firstname, lastname');
    }

    function get_user_fullname(\stdClass $user) : string
    {
        $temp = explode(' ', $user->firstname);
        $str = ' ';

        foreach($temp as $key2 => $name)
        {
            $str .= mb_substr($name, 0, 1).'.';
        }

        return $user->lastname.$str;
    }

    function get_user_shortname(\stdClass $user) : string
    {
        $temp = explode(' ', $user->firstname);
        
        $str = '';
        $str.= mb_substr($user->lastname, 0, 4).'.';

        foreach($temp as $key2 => $name)
        {
            $str .= mb_substr($name, 0, 1).'.';
        }

        return $str;
    }

    function get_theme_name(int $id) : string 
    {
        global $DB;
        return $DB->get_field('coursework_themes', 'name', array('id'=>$id));
    }


    function get_users_with_names_from_ids_array(array $usersIds) : array
    {
        $users = array();
        foreach($usersIds as $id)
        {
            $users[] = get_user_stdclass($id);
        }

        usort($users, 'sort_user_fullnames');

        return $users;
    }

    function send_notification( \stdClass $cm,
                                \stdClass $course, 
                                string $messageName,
                                \stdClass $userFrom,
                                \stdClass $userTo, 
                                string $headerMessage, 
                                string $fullMessageHtml) : void 
    {
        global $CFG;

        $message = new \core\message\message();
        $message->component = 'mod_coursework';
        $message->name = $messageName;
        $message->userfrom = $userFrom;
        $message->userto = $userTo;
        $message->subject = $headerMessage;
        $message->fullmessage = $headerMessage;
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml = $fullMessageHtml;
        $message->smallmessage = $headerMessage;
        $message->notification = '1';
        $message->contexturl = $CFG->wwwroot.'/coursework/view.php?id='.$cm->id;
        $message->contexturlname = cw_get_coursework_name($cm->instance);
        $message->courseid = $course->id;

        message_send($message);
    }

    function get_user($id)
    {
        global $DB;
        return $DB->get_record('user', array('id' => $id));
    }

    function get_coursework($id)
    {
        global $DB;
        return $DB->get_record('coursework', array('id'=> $id));
    }

}

namespace
{
    function sort_user_fullnames($a, $b)
    {
        return strcmp($a->fullname, $b->fullname);
    }
}


