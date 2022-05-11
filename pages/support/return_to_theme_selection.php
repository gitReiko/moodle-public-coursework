<?php

require_once '../../../../config.php';
require_once '../../classes/support/return_to_theme_selection/main.php';
require_once '../../lib/database/add_new_student_work_status.php';
require_once '../../lib/getters/students_getter.php';
require_once '../../lib/getters/teachers_getter.php';
require_once '../../lib/getters/common_getter.php';
require_once '../../lib/getters/user_getter.php';
require_once '../../lib/notification.php'; 
require_once '../../lib/feedbacker.php';
require_once '../../lib/common.php';
require_once '../../lib/enums.php';

use Coursework\Support\ReturnToThemeSelection\Main as returnToThemeSelection;

$id = required_param('id', PARAM_INT);    // Course Module ID
 
if (!$cm = get_coursemodule_from_id('coursework', $id)) {
    print_error('Course Module ID was incorrect'); // NOTE this is invalid use of print_error, must be a lang string id
}
if (!$course = $DB->get_record('course', array('id'=> $cm->course))) {
    print_error('course is misconfigured');  // NOTE As above
}

$url = new moodle_url(returnToThemeSelection::MODULE_URL, array('id'=>$id));
$PAGE->set_url($url);

$PAGE->set_context(context_module::instance($cm->id));
$PAGE->set_pagelayout('incourse');
$PAGE->set_cm($cm);

$PAGE->set_title(get_string('return_to_theme_selection', 'coursework'));
$PAGE->set_heading(get_string('return_to_theme_selection', 'coursework'));

$PAGE->requires->js('/mod/coursework/js/support/return_to_theme_selection.js');
$PAGE->requires->css('/mod/coursework/css/lib/students_mass_actions.css');
$PAGE->requires->js('/mod/coursework/js/lib/mass_actions_on_students.js');
$PAGE->requires->css('/mod/coursework/css/common.css');

require_login();

echo $OUTPUT->header();

$module = new returnToThemeSelection($course, $cm);
echo $module->get_page();

echo $OUTPUT->footer();

