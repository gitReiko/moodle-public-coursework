<?php 

namespace coursework_lib;

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

function get_teachers(int $courseworkId)
{
    global $DB;
    $sql = 'SELECT ct.id as recordid, ct.teacher as teacherid, u.firstname, u.lastname, 
                ct.course as courseid, c.fullname as coursename, ct.quota
            FROM {coursework_teachers} as ct, {user} as u, {course} as c
            WHERE ct.teacher = u.id AND ct.course = c.id AND coursework = ?';
    $conditions = array($courseworkId);

    $teachers = $DB->get_records_sql($sql, $conditions);
    $teachers = cw_add_fullnames_to_users_array($teachers);

    usort($teachers, function($a, $b)
    {
        return strcmp($a->fullname, $b->fullname);
    });

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

    usort($users, function($a, $b)
    {
        return strcmp($a->fullname, $b->fullname);
    });

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

function get_using_task(\stdClass $cm)
{
    global $DB;
    $sql = 'SELECT ct.*, ctu.id AS usingtaskid
            FROM {coursework_tasks} AS ct
            INNER JOIN {coursework_default_task_use} AS ctu
            ON ct.id = ctu.task
            WHERE coursework = ?';
    $conditions = array($cm->instance);
    return $DB->get_record_sql($sql, $conditions);
}

function get_task_sections(int $taskId)
{
    global $DB;
    $conditions = array('task' => $taskId);
    return $DB->get_records('coursework_tasks_sections', $conditions, 'listposition, name');
}

function get_user_task($cm, $userId) : \stdClass 
{
    global $DB;
    $taskId = get_user_task_id($cm, $userId);
    return $DB->get_record('coursework_tasks', array('id'=>$taskId));
}

function get_user_task_id($cm, $userId) : int 
{
    global $DB;
    $where = array('coursework'=>$cm->instance, 'student'=>$userId);
    return $DB->get_field('coursework_students', 'task', $where);
}

function get_sections_to_check($cm, $userId)
{
    $taskId = get_user_task_id($cm, $userId);

    global $DB;
    $sql = 'SELECT * 
            FROM {coursework_tasks_sections} 
            WHERE task = ?
            AND completiondate IS NOT NULL 
            AND completiondate != 0
            ORDER BY listposition';
    $params = array($taskId);
    return $DB->get_records_sql($sql, $params);
}

function is_section_status_exist($cm, $studentId, $sectionId) : bool 
{
    global $DB;
    $where = array('coursework'=>$cm->instance, 
                    'student' => $studentId,
                    'section' => $sectionId);
    return $DB->record_exists('coursework_sections_status', $where);
}

function get_student_section_status($cm, $studentId, $sectionId) : \stdClass  
{
    global $DB;
    $where = array('coursework'=>$cm->instance, 
                    'student' => $studentId,
                    'section' => $sectionId);
    return $DB->get_record('coursework_sections_status', $where);
}

function get_student_work(\stdClass $cm, int $studentId) : \stdClass 
{
    global $DB;
    $where = array('coursework'=>$cm->instance, 'student' => $studentId);
    return $DB->get_record('coursework_students', $where);
}

function get_back_to_course_button(int $courseId)
{
    $btn = '<a href="/course/view.php?id='.$courseId.'">';
    $btn.= '<button form="sdvsre453">'.get_string('back_to_course', 'coursework').'</button>';
    $btn.= '</a>';
    return $btn;
}

function get_back_to_works_list_button(\stdClass $cm) : string 
{
    $btn = '<a href="/mod/coursework/view.php?id='.$cm->id.'">';
    $btn.= '<button form="sdvsre453">'.get_string('back_to_works_list', 'coursework').'</button>';
    $btn.= '</a>';
    return $btn;
}

function get_student_work_status(\stdClass $cm, int $studentId) : string 
{
    global $DB;
    $where = array('coursework'=>$cm->instance, 'student' => $studentId);
    return $DB->get_field('coursework_students', 'status', $where);
}

function is_student_work_not_ready_or_need_to_fix(\stdClass $cm, int $studentId) : bool 
{
    $status = get_student_work_status($cm, $studentId);

    if($status === NOT_READY) return true;
    else if($status === NEED_TO_FIX) return true;
    else return false;
}

function get_leader_available_quota(\stdClass $cm, int $teacherId, int $courseId) : int 
{
    $totalQuota = get_leader_total_quota($cm, $teacherId, $courseId);
    $usedQuota = get_leader_used_quota($cm, $teacherId, $courseId);

    return ($totalQuota - $usedQuota);
}

function is_teacher_quota_gone(\stdClass $cm, int $teacherId, int $courseId) : bool 
{
    $totalQuota = get_leader_total_quota($cm, $teacherId, $courseId);
    $usedQuota = get_leader_used_quota($cm, $teacherId, $courseId);

    if(($totalQuota - $usedQuota) < 1)
    {
        return true;
    }
    else 
    {
        return false;
    }
}

function get_leader_used_quota(\stdClass $cm, int $teacherId, int $courseId) : int 
{
    $students = get_coursework_students($cm);

    $usedQuota = 0;
    foreach($students as $student)
    {
        if(is_leader_used_by_student($cm, $student->id, $teacherId, $courseId))
        {
            $usedQuota++;
        }
    }
    
    return $usedQuota;
}

function is_leader_used_by_student(\stdClass $cm, int $studentId, int $teacherId, int $courseId) : bool 
{
    global $DB;
    $conditions = array
    (
        'coursework' => $cm->instance, 
        'student' => $studentId,
        'teacher' => $teacherId, 
        'course' => $courseId
    );
    return $DB->record_exists('coursework_students', $conditions);
}

function get_leader_total_quota(\stdClass $cm, int $teacherId, int $courseId) : int
{
    global $DB;
    $conditions = array
    (
        'coursework' => $cm->instance,
        'teacher' => $teacherId,
        'course' => $courseId
    );
    return $DB->get_field('coursework_teachers', 'quota', $conditions);
}


