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







