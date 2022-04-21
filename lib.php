<?php

require_once 'lib/getters/common_getter.php';
require_once 'lib/cleaner.php';

use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\Cleaner;

function coursework_add_instance($coursework)
{
    global $DB;
    $coursework->timemodified = time();

    if(empty($coursework->usetask)) $coursework->usetask = 0;
    if(empty($coursework->autotaskissuance)) $coursework->autotaskissuance = 0;
    if(empty($coursework->maxfilesize)) $coursework->maxfilesize = 0;
    if(empty($coursework->maxfilesnumber)) $coursework->maxfilesnumber = 0;

    return $DB->insert_record('coursework', $coursework);
}

function coursework_update_instance($coursework)
{
    global $DB;

    $coursework->id = $coursework->instance;
    $coursework->timemodified = time();

    if(empty($coursework->usetask)) $coursework->usetask = 0;
    if(empty($coursework->autotaskissuance)) $coursework->autotaskissuance = 0;
    if(empty($coursework->maxfilesize)) $coursework->maxfilesize = 0;
    if(empty($coursework->maxfilesnumber)) $coursework->maxfilesnumber = 3;

    if($DB->update_record('coursework', $coursework)) return true;
    else return false;
}

function coursework_delete_instance($courseworkId)
{
    $students = get_coursework_students($courseworkId);

    foreach($students as $value)
    {
        $cleaner = new Cleaner($courseworkId);
        $cleaner->delete_all_student_data($value->student);
    }

    delete_from_coursework_themes_collections_use($courseworkId);
    delete_from_coursework_teachers($courseworkId);

    coursework_grade_item_delete(cg::get_coursework($courseworkId));

    delete_from_coursework($courseworkId);

    return true;
}

function get_coursework_students(int $courseworkId)
{
    global $DB;
    $where = array('coursework' => $courseworkId);
    return $DB->get_records('coursework_students', $where);
}

function delete_from_coursework_themes_collections_use(int $courseworkId)
{
    global $DB;
    $where = array('coursework' => $courseworkId);
    return $DB->delete_records('coursework_themes_collections_use', $where);
}

function delete_from_coursework_teachers(int $courseworkId)
{
    global $DB;
    $where = array('coursework' => $courseworkId);
    return $DB->delete_records('coursework_teachers', $where);
}

function delete_from_coursework(int $courseworkId)
{
    global $DB;
    $where = array('id' => $courseworkId);
    return $DB->delete_records('coursework', $where);
}

function coursework_extend_settings_navigation($settings, $navref)
{
    global $PAGE, $DB;

    // We want to add these new nodes after the Edit settings node, and before the
    // Locally assigned roles node. Of course, both of those are controlled by capabilities.
    $keys = $navref->get_children_key_list();
    $beforekey = null;
    $i = array_search('modedit', $keys);
    if($i === false and array_key_exists(0, $keys))
    {
        $beforekey = $keys[0];
    } 
    else if(array_key_exists($i + 1, $keys)) 
    {
        $beforekey = $keys[$i + 1];
    }

    $cm = $PAGE->cm;
    if(!$cm) 
    {
        return;
    }

    $context = $cm->context;
    $course = $PAGE->course;

    if(!$course)
    {
        return;
    }

    if(has_capability('mod/coursework:overviewquota', $PAGE->cm->context))
    {
        $link = new moodle_url('/mod/coursework/pages/quota_overview.php', array('id' => $cm->id));
        $linkname = get_string('quota_overview', 'coursework');
        $navref->add($linkname, $link, navigation_node::TYPE_SETTING);
    }

    if(is_user_can_view_configuration_category())
    {
        $link = new moodle_url('/mod/coursework/pages/config/list.php', array('id' => $cm->id));
        $linkname = get_string('configuration', 'coursework');
        $confCategory = $navref->add($linkname, $link, navigation_node::TYPE_CONTAINER);

        if(has_capability('mod/coursework:settingleaders', $PAGE->cm->context))
        {
            $link = new moodle_url('/mod/coursework/pages/config/appoint_leaders.php', array('id' => $cm->id));
            $linkname = get_string('appoint_leaders', 'coursework');
            $confCategory->add($linkname, $link, navigation_node::TYPE_SETTING);
        }

        if(has_capability('mod/coursework:distributetoleaders', $PAGE->cm->context))
        {
            $link = new moodle_url('/mod/coursework/pages/config/distribute_to_leaders.php', array('id' => $cm->id));
            $linkname = get_string('distribute_to_leaders', 'coursework');
            $confCategory->add($linkname, $link, navigation_node::TYPE_SETTING);
        }

        if(has_capability('mod/coursework:setdefaulttasktemplate', $PAGE->cm->context))
        {
            $link = new moodle_url('/mod/coursework/pages/config/set_default_task_template.php', array('id' => $cm->id));
            $linkname = get_string('set_default_task_template', 'coursework');
            $confCategory->add($linkname, $link, navigation_node::TYPE_SETTING);
        }

        if(has_capability('mod/coursework:setsuggestedthemes', $PAGE->cm->context))
        {
            $link = new moodle_url('/mod/coursework/pages/config/set_suggested_themes.php', array('id' => $cm->id));
            $linkname = get_string('set_suggested_themes', 'coursework');
            $confCategory->add($linkname, $link, navigation_node::TYPE_SETTING);
        }
    }

    if(is_user_can_view_support_category())
    {
        $link = new moodle_url('/mod/coursework/pages/support/list.php', array('id' => $cm->id));
        $linkname = get_string('support', 'coursework');
        $mainCategory = $navref->add($linkname, $link, navigation_node::TYPE_CONTAINER);

        if(has_capability('mod/coursework:returntothemeselection', $PAGE->cm->context))
        {
            $link = new moodle_url('/mod/coursework/pages/support/return_to_theme_selection.php', array('id' => $cm->id));
            $linkname = get_string('return_to_theme_selection', 'coursework');
            $mainCategory->add($linkname, $link, navigation_node::TYPE_SETTING);
        }

        if(has_capability('mod/coursework:return_work_for_rework', $PAGE->cm->context))
        {
            $link = new moodle_url('/mod/coursework/pages/support/return_work_for_rework.php', array('id' => $cm->id));
            $linkname = get_string('return_work_for_rework', 'coursework');
            $mainCategory->add($linkname, $link, navigation_node::TYPE_SETTING);
        }

        if(has_capability('mod/coursework:leaderreplacement', $PAGE->cm->context))
        {
            $link = new moodle_url('/mod/coursework/pages/support/leader_replacement.php', array('id' => $cm->id));
            $linkname = get_string('leader_replacement', 'coursework');
            $mainCategory->add($linkname, $link, navigation_node::TYPE_SETTING);
        }

        if(has_capability('mod/coursework:deletestudentcoursework', $PAGE->cm->context))
        {
            $link = new moodle_url('/mod/coursework/pages/support/delete_student_coursework.php', array('id' => $cm->id));
            $linkname = get_string('delete_student_coursework', 'coursework');
            $mainCategory->add($linkname, $link, navigation_node::TYPE_SETTING);
        }
    }

    if(has_capability('mod/coursework:taskstemplatesmanagement', $PAGE->cm->context))
    {
        $link = new moodle_url('/mod/coursework/pages/tasks_templates_management.php', array('id' => $cm->id));
        $linkname = get_string('tasks_templates_management', 'coursework');
        $navref->add($linkname, $link, navigation_node::TYPE_SETTING);
    }

    if(has_capability('mod/coursework:themescollectionsmanagement', $PAGE->cm->context))
    {
        $link = new moodle_url('/mod/coursework/pages/themes_collections_management.php', array('id' => $cm->id));
        $linkname = get_string('themes_collections_management', 'coursework');
        $navref->add($linkname, $link, navigation_node::TYPE_SETTING);
    }

    if(has_capability('mod/coursework:manage_own_old_files_area', $PAGE->cm->context))
    {
        $link = new moodle_url('/mod/coursework/pages/manage_old_files_area.php', array('id' => $cm->id));
        $linkname = get_string('manage_old_files_area', 'coursework');
        $navref->add($linkname, $link, navigation_node::TYPE_SETTING);
    }

}

function is_user_can_view_configuration_category() : bool
{
    global $PAGE;

    if(has_capability('mod/coursework:settingleaders', $PAGE->cm->context))
    {
        return true;
    }
    else if(has_capability('mod/coursework:distributetoleaders', $PAGE->cm->context))
    {
        return true;
    }
    else if(has_capability('mod/coursework:setdefaulttasktemplate', $PAGE->cm->context))
    {
        return true;
    }
    else if(has_capability('mod/coursework:setsuggestedthemes', $PAGE->cm->context))
    {
        return true;
    }

    return false;
}

function is_user_can_view_support_category() : bool
{
    global $PAGE;

    if(has_capability('mod/coursework:leaderreplacement', $PAGE->cm->context))
    {
        return true;
    }
    else if(has_capability('mod/coursework:deletestudentcoursework', $PAGE->cm->context))
    {
        return true;
    }
    else if(has_capability('mod/coursework:return_work_for_rework', $PAGE->cm->context))
    {
        return true;
    }
    else if(has_capability('mod/coursework:returntothemeselection', $PAGE->cm->context))
    {
        return true;
    }

    return false;
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
            return true;
        case FEATURE_GRADE_OUTCOMES:
            return true;
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

function coursework_grade_item_update($coursework, $grades=NULL) {
    global $CFG;
    if (!function_exists('grade_update')) { //workaround for buggy PHP versions
        require_once($CFG->libdir.'/gradelib.php');
    }
 
    return grade_update('mod/coursework', $coursework->course, 'mod', 'coursework', $coursework->id, 0, $grades);
}

function coursework_update_grades($coursework, $userid=0, $nullifnone=true) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');
 
    if (!$coursework->assessed) {
        coursework_grade_item_update($coursework);
 
    } else if ($grades = coursework_get_user_grades($coursework, $userid)) {
        coursework_grade_item_update($coursework, $grades);
 
    } else if ($userid and $nullifnone) {
        $grade = new stdClass();
        $grade->userid   = $userid;
        $grade->rawgrade = NULL;
        coursework_grade_item_update($coursework, $grade);
 
    } else {
        coursework_grade_item_update($coursework);
    }
}

function coursework_grade_item_delete($coursework) {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    return grade_update(
        'mod/coursework', 
        $coursework->course, 
        'mod', 
        'coursework', 
        $coursework->id, 
        0,
        null, 
        array('deleted' => 1)
    );
}
