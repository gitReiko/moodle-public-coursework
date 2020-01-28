<?php namespace coursework_lib;

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

// Notifications functions\

function send_notification(\stdClass $cm, int $courseid, string $messagename, \stdClass $userto, string $headerMessage, string $htmlMessage) : void 
{
    global $CFG, $USER;

    $message = new \core\message\message();
    $message->component = 'mod_coursework';
    $message->name = $messagename;
    $message->userfrom = $USER;
    $message->userto = $userto;
    $message->subject = $headerMessage;
    $message->fullmessage = $headerMessage;
    $message->fullmessageformat = FORMAT_MARKDOWN;
    $message->fullmessagehtml = $htmlMessage;
    $message->smallmessage = $headerMessage;
    $message->notification = '1';
    $message->contexturl = $CFG->wwwroot.'/coursework/view.php?id='.$cm->id;
    $message->contexturlname = cw_get_coursework_name($cm->instance);
    $message->courseid = $courseid;

    message_send($message);
}


