<?php

// Moodle module functions

function coursework_add_instance($coursework)
{
    global $DB;
    $coursework->timemodified = time();
    return $DB->insert_record('coursework', $coursework);
}

function coursework_update_instance($coursework)
{
    global $DB;

    $coursework->id = $coursework->instance;
    $coursework->timemodified = time();

    if($DB->update_record('coursework', $coursework)) return true;
    else return true;
}

function coursework_delete_instance($id)
{
    global $DB;

    if ($DB->record_exists('coursework', array('id'=>$id)))
    {
        $DB->delete_records('coursework_groups', array('coursework'=>$id));
        $DB->delete_records('coursework_students', array('coursework'=>$id));
        $DB->delete_records('coursework_tutors', array('coursework'=>$id));
        $DB->delete_records('coursework', array('id'=>$id));

        return true;
    }
    else
    {
        return false;
    }
}

function coursework_extend_settings_navigation($settings, $navref)
{
    global $PAGE, $DB;

    // We want to add these new nodes after the Edit settings node, and before the
    // Locally assigned roles node. Of course, both of those are controlled by capabilities.
    $keys = $navref->get_children_key_list();
    $beforekey = null;
    $i = array_search('modedit', $keys);
    if ($i === false and array_key_exists(0, $keys)) {
        $beforekey = $keys[0];
    } else if (array_key_exists($i + 1, $keys)) {
        $beforekey = $keys[$i + 1];
    }

    $cm = $PAGE->cm;
    if (!$cm) {
        return;
    }

    $context = $cm->context;
    $course = $PAGE->course;

    if (!$course) {
        return;
    }

    if (has_capability('mod/coursework:enrollmembers', $PAGE->cm->context))
    {
        $link = new moodle_url('/mod/coursework/enrollmembers.php', array('id' => $cm->id));
        $linkname = get_string('configurate_coursework', 'coursework');
        $node = $navref->add($linkname, $link, navigation_node::TYPE_SETTING);
    }
}

// General coursework functions
function cw_get_user_name(int $id) : string
{
    global $DB;

    $user = $DB->get_record('user', array('id'=>$id), 'id, firstname, lastname');

    $temp = explode(' ', $user->firstname);
    $str = ' ';

    foreach($temp as $key2 => $name)
    {
        $str .= mb_substr($name, 0, 1).'.';
    }

    return $user->lastname.$str;
}

function cw_get_theme_name(int $id) : string
{
    global $DB;
    $theme = $DB->get_record('coursework_themes', array('id'=>$id));
    return $theme->name;
}

function cw_get_user_groups_names(int $course, int $user) : string
{
    $str = '';

    $groups = groups_get_user_groups($course, $user);

    for($i = 0; $i < count($groups); $i++)
    {
        for($j = 0; $j < count($groups[$i]); $j++)
        {
            $name = groups_get_group_name($groups[$i][$j]);

            if($j) $str .= '<br>';
            $str.= $name;
        }
    }

    return $str;
}

function cw_get_coursework_students(int $coursework, int $student)
{
    global $DB;
    $conditions = array('coursework' => $coursework, 'student' => $student);
    return $DB->get_record('coursework_students', $conditions);
}

function cw_add_user_names($usersid) : array
{
    $users = array();

    foreach($usersid as $userid)
    {
        $user = new stdClass;
        $user->id = $userid;
        $user->name = cw_get_user_name($userid);

        $users[] = $user;
    }

    return $users;
}

function cw_cmp_users(stdClass $a, stdClass $b) : int
{
    if ($a->name == $b->name) return 0;
    else return ($a->name < $b->name) ? -1 : 1;
}

function cw_get_course_name($id) : string
{
    global $DB;
    $course = $DB->get_record('course', array('id'=>$id), 'fullname');

    $str = '';
    $str.= $course->fullname;

    return $str;
}

function cw_get_coursework_students_id($cm, $student) : int
{
    global $DB;
    $student = optional_param(ECM_STUDENTS, 0, PARAM_INT);
    $conditions = array('coursework'=>$cm, 'student'=>$student);
    $coursework = $DB->get_record('coursework_students', $conditions);

    if(isset($coursework->id) && $coursework->id) return $coursework->id;
    else return 0;
}

function cw_get_user_photo($userID) : string
{
	global $DB, $OUTPUT;

	$user = $DB->get_record('user', array('id' => $userID));
	$photo = $OUTPUT->user_picture($user);

	return $photo;
}

function cw_get_coursework_name($id) : string
{
    global $DB;
    $coursework = $DB->get_record('coursework', array('id'=> $id), 'name');

    if(isset($coursework->name) && $coursework->name) return $coursework->name;
    else return '';
}


function cw_get_html_message($cm, $course, $message, $notifications) : string
{
    global $CFG, $USER;

    // Tree of links
    $str = '<p>';
    $str.= '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$course.'">'.cw_get_course_name($course).'</a>->';
    $str.= '<a href="'.$CFG->wwwroot.'/mod/coursework/index.php?id='.$course.'">'.get_string('modulenameplural', 'coursework').'</a>->';
    $str.= '<a href="'.$CFG->wwwroot.'/mod/coursework/view.php?id='.$cm->id.'">'.cw_get_coursework_name($cm->instance);
    $str.= '</p>';

    // Delimiter
    $str.= '<hr>';

    // Message
    $str.= $message;

    // Link on coursework
    $str.= '<p>';
    $str.= get_string('coursework_link1','coursework');
    $str.= '<a href="'.$CFG->wwwroot.'/mod/coursework/view.php?id='.$cm->id.'">';
    $str.= get_string('coursework_link2','coursework').'</a>';
    $str.= '</p>';

    // Delimiter
    $str.= '<hr>';

    //Notifications
    $str.= $notifications;

    return $str;
}

function cw_print_error_message(string $message) : void
{
    echo '<p style="background-color:LightCoral; padding:10px;">'.$message.'</p>';
}

function cw_is_user_have_student_role_in_course(int $userid, int $course) : bool
{
    $roles = get_user_roles(context_course::instance($course), $userid);
    foreach($roles as $role)
    {
        if($role->roleid == STUDENT_ROLE) return true;
    }
    return false;
}

function cw_get_tutor_records(int $courseworkID) : array 
{
    global $DB;
    $tutorsRecords = array();
    $tutorsRecords = $DB->get_records('coursework_tutors', array('coursework'=>$courseworkID));
    return $tutorsRecords;
}

