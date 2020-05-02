<?php

use coursework_lib as lib;
// Moodle module functions

function coursework_add_instance($coursework)
{
    global $DB;
    $coursework->timemodified = time();

    if(empty($coursework->usetask)) $coursework->usetask = 0;
    if(empty($coursework->automatictaskobtaining)) $coursework->automatictaskobtaining = 0;

    return $DB->insert_record('coursework', $coursework);
}

function coursework_update_instance($coursework)
{
    global $DB;

    $coursework->id = $coursework->instance;
    $coursework->timemodified = time();

    if(empty($coursework->usetask)) $coursework->usetask = 0;
    if(empty($coursework->automatictaskobtaining)) $coursework->automatictaskobtaining = 0;

    if($DB->update_record('coursework', $coursework)) return true;
    else return false;
}

function coursework_delete_instance($id)
{
    global $DB;

    if ($DB->record_exists('coursework', array('id'=>$id)))
    {
        $DB->delete_records('coursework_students', array('coursework'=>$id));
        $DB->delete_records('coursework_teachers', array('coursework'=>$id));
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
        $link = new moodle_url('/mod/coursework/configuration.php', array('id' => $cm->id));
        $linkname = get_string('configurate_coursework', 'coursework');
        $node = $navref->add($linkname, $link, navigation_node::TYPE_SETTING);
    }
}

/**
 * Return the list if Moodle features this module supports
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, null if doesn't know
 */
function coursework_supports($feature) {
    switch($feature) {
        case FEATURE_GROUPS:
            return true;
        case FEATURE_GROUPINGS:
            return true;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_COMPLETION_HAS_RULES:
            return false;
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_GRADE_OUTCOMES:
            return false;
        case FEATURE_BACKUP_MOODLE2:
            return false;
        case FEATURE_SHOW_DESCRIPTION:
            return false;
        case FEATURE_ADVANCED_GRADING:
            return false;
        case FEATURE_PLAGIARISM:
            return false;
        case FEATURE_COMMENT:
            return false;

        default:
            return null;
    }
}

/**
 * Serve the files from the coursework file areas
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if the file not found, just send the file otherwise and do not return anything
 */
function coursework_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) 
{
    // Check the contextlevel is as expected
    if ($context->contextlevel != CONTEXT_MODULE) 
    {
        return false; 
    }

    // Make sure the filearea is one of those used by the plugin.
    if ($filearea !== 'student' && $filearea !== 'teacher') 
    {
        return false;
    }

    // Make sure the user is logged in and has access to the module (plugins that are not course modules should leave out the 'cm' part).
    require_login($course, true, $cm);

    // Check the relevant capabilities
    if($filearea === 'student')
    {
        if (!has_capability('mod/coursework:is_student', $context)) 
        {
            return false;
        }
    }
    else if($filearea === 'teacher')
    {
        if (!has_capability('mod/coursework:is_teacher', $context)) 
        {
            return false;
        }
    }

    // The first item in the $args array.
    $itemid = array_shift($args); 

    // Extract the filename / filepath from the $args array.
    $filename = array_pop($args); // The last item in the $args array.
    if(!$args) 
    {
        $filepath = '/'; // $args is empty => the path is '/'
    } 
    else 
    {
        $filepath = '/'.implode('/', $args).'/'; // $args contains elements of the filepath
    }

    // Retrieve the file from the Files API.
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'mod_coursework', $filearea, $itemid, $filepath, $filename);

    if(!$file) 
    {
        return false; // The file does not exist.
    }

    send_stored_file($file);
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
    if(isset($theme->name)) return $theme->name;
}

function cw_get_user_groups_names(int $course, int $user) : string
{
    $str = '';

    $groups = groups_get_user_groups($course, $user);

    // Reset is used because the groups array contains two identical arrays.
    foreach(reset($groups) as $group)
    {
        $name = groups_get_group_name($group);

        if($str != '') $str .= '<br>';
        $str.= $name;     
    }

    return $str;
}

function cw_get_coursework_student(int $coursework, int $student)
{
    global $DB;
    $conditions = array('coursework' => $coursework, 'student' => $student);
    return $DB->get_record('coursework_students', $conditions);
}

function cw_get_course_name($id) : string
{
    global $DB;
    $course = $DB->get_record('course', array('id'=>$id), 'fullname');

    $str = '';
    $str.= $course->fullname;

    return $str;
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


function cw_get_html_message(stdClass $cm, int $course, string $message, string $notifications) : string
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

// New refactoring
// Database functions
function cw_get_coursework_teachers(int $courseworkID) : array 
{
    global $DB;
    $sql = 'SELECT ct.id, ct.teacher, ct.course, c.fullname as coursename, ct.quota, u.firstname, u.lastname
        FROM {coursework_teachers} as ct, {user} as u, {course} as c
        WHERE ct.teacher = u.id AND ct.course = c.id
            AND u.suspended = 0 AND ct.coursework = ?
        ORDER BY u.lastname';
    $conditions = array($courseworkID);
    $teachers = array();
    $teachers = $DB->get_records_sql($sql, $conditions);
    return $teachers;
}

function cw_get_all_course_groups(int $courseID) : array
{
    global $DB;
    $groups = array();
    $groups = $DB->get_records('groups', array('courseid'=>$courseID), 'name', 'id, name');
    $groups = cw_add_students_count_to_groups($groups, $courseID);
    return $groups;
}

function cw_add_students_count_to_groups(array $groups, int $courseid) : array 
{
    $studentArchetypeRoles = cw_get_archetype_roles(array('student'));

    foreach($groups as $group)
    {
        $members = cw_get_group_members($group->id);
        $studentsCount = 0;

        foreach($members as $member)
        {
            $memberRoles = get_user_roles(context_course::instance($courseid), $member->id);

            if(cw_is_user_archetype($memberRoles, $studentArchetypeRoles)) $studentsCount++;
        }

        $group->studentsCount = $studentsCount++;
    }

    return $groups;
}

function cw_get_all_courses() : array
{
    global $DB;
    $courses = array();
    $courses = $DB->get_records('course', array(), 'fullname', 'id, fullname');
    return $courses;
}

function cw_get_coursework_courses(int $courseworkID) : array 
{
    global $DB;
    $courses = array();
    $sql = 'SELECT DISTINCT ct.course, c.id, c.fullname
            FROM {coursework_teachers} AS ct, {course} AS c
            WHERE ct.course = c.id AND ct.coursework = ?
            ORDER BY c.fullname';
    $conditions = array($courseworkID);
    $courses = $DB->get_records_sql($sql, $conditions);
    return $courses;
}

/**
 * Returns activity users with a certain archetypes.
 * 
 * @param array $archetypes - list of user archetypes
 * @param stdClass $cm - moodle course module object
 * @param int $courseId - id of course
 * 
 * @return array of users -> array (stdClass (id, firstname, lastname, fullname))
 */
function cw_get_coursework_users_with_archetypes_roles(array $archetypes, stdClass $cm, int $courseId) : array 
{
    lib\get_coursework_students($cm);
    $archetypesRoles = cw_get_archetype_roles($archetypes);
    $groups = groups_get_activity_allowed_groups($cm);
    return cw_get_users_with_archetype_roles_from_group($groups, $archetypesRoles, $courseId, $cm->instance);
}


function cw_get_users_with_archetype_roles_from_group(array $groups, array $usersArchetypeRoles, int $courseID, int $CourseModuleID) : array 
{
    $users = array();
    foreach($groups as $group)
    {
        $members = cw_get_group_members($group->id);

        foreach($members as $member)
        {
            $memberRoles = get_user_roles(context_course::instance($courseID), $member->id);

            if(cw_is_user_archetype($memberRoles, $usersArchetypeRoles))
            {
                $users[] = $member;
            }
        }
    }

    $users = cw_array_unique_for_stdclass($users);
    usort($users, "compare_user_fullnames");

    return $users;
}

function cw_array_unique_for_stdclass(array $array) : array
{
    $array = array_map('json_encode', $array);
    $array = array_unique($array);
    $array = array_map('json_decode', $array);
    return $array;
}

function compare_user_fullnames($a, $b)
{
    return strcmp($a->fullname, $b->fullname);
}

function cw_get_group_members(int $groupid) : array 
{
    global $DB;
    $sql = 'SELECT gm.userid as id, u.firstname, u.lastname
        FROM {groups_members} as gm, {user} as u
        WHERE gm.userid = u.id AND u.suspended = 0 AND gm.groupid = ?
        ORDER BY u.lastname';
    $conditions = array($groupid);
    $groupMembers = array();
    $groupMembers = $DB->get_records_sql($sql, $conditions);
    $groupMembers = cw_add_fullnames_to_users_array($groupMembers);
    return $groupMembers;
}

function cw_add_fullnames_to_users_array(array $users) : array
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

function cw_get_archetype_roles(array $archetypes) : array 
{
    $archCount = count($archetypes);

    if($archCount);
    {
        global $DB;
        $sql = 'SELECT id FROM {role} WHERE archetype = ? ';
        
        if($archCount > 1)
        {
            for($i = 1; $i < $archCount; $i++)
            {
                $sql.= ' OR archetype = ? ';
            }
        }

        return $DB->get_records_sql($sql, $archetypes);
    }
}

function cw_is_user_archetype(array $userRoles, array $archetypeRoles) : bool 
{
    foreach($userRoles as $userRole)
    {
        foreach($archetypeRoles as $archetypeRole)
        {
            if($userRole->roleid == $archetypeRole->id) return true;
        }
    }

    return false;
}

function cw_prepare_data_for_message() : stdClass 
{
    global $USER;
    $data = new stdClass;
    $data->teacher = cw_get_user_name($USER->id);
    $data->date = date('d-m-Y');
    $data->time = date('G:i');
    return $data;
}

function cw_is_teacher_has_quota($cm, int $teacherid, int $courseID) : bool 
{
    global $DB;

    $teacherRecords = $DB->get_records('coursework_teachers', array('coursework'=>$cm->instance, 'teacher'=>$teacherid));
    
    $totalQuota = 0;
    $courseQuota = 0;
    foreach($teacherRecords as $teacher)
    {
        $totalQuota += $teacher->quota;
        if((int)$teacher->course === $courseID) $courseQuota += $teacher->quota;
    }

    $usedTotalQuota = cw_get_teacher_total_quota($cm, $teacherid);
    $usedCourseQuota = cw_get_teacher_course_total_quota($cm, $teacherid, $courseID);

    if(($totalQuota - $usedTotalQuota) > 0)
    {
        if(($courseQuota - $usedCourseQuota) > 0) return true;
    }
    else if(cw_is_this_teacher_already_chosen_for_this_student($cm->instance, $teacherid))
    {
        return true;
    }

    return false;
}

function cw_get_teacher_total_quota($cm, $teacherid)
{
    global $DB;
    $students = lib\get_coursework_students_for_in_query($cm);
    $sql = "SELECT id
            FROM {coursework_students} 
            WHERE coursework = ?
            AND teacher = ?
            AND student IN ($students)";

    $params = array($cm->instance, $teacherid);

    $result = $DB->get_records_sql($sql, $params);
    
    if(empty($result)) return 0;
    else return count($result);
}

function cw_get_teacher_course_total_quota($cm, $teacherid, $courseID)
{
    global $DB;
    $students = lib\get_coursework_students_for_in_query($cm);
    $sql = "SELECT id
            FROM {coursework_students} 
            WHERE coursework = ?
            AND teacher = ?
            AND course = ?
            AND student IN ($students)";
    $params = array($cm->instance, $teacherid, $courseID);
    
    $result = $DB->get_records_sql($sql, $params);
    
    if(empty($result)) return 0;
    else return count($result);
}

function cw_is_this_teacher_already_chosen_for_this_student(int $courseworkID, int $teacherid) : bool 
{
    global $DB, $USER;
    $conditions = array('coursework'=>$courseworkID, 'student'=>$USER->id ,'teacher'=>$teacherid);
    if($DB->record_exists('coursework_students', $conditions)) return true;
    else return false;
}







