<?php

require_once '../../../../config.php';
require_once '../../classes/support/delete_student_coursework/main.php';
require_once '../../lib/getters/students_getter.php';
require_once '../../lib/getters/courses_getter.php';
require_once '../../lib/getters/common_getter.php';
require_once '../../lib/getters/user_getter.php';
require_once '../../lib/notification.php'; 
require_once '../../lib/feedbacker.php';
require_once '../../lib/cleaner.php';
require_once '../../lib/common.php';
require_once '../../lib/enums.php';

use Coursework\Support\DeleteStudentCoursework as deleteStudentCoursework;
 
$id = required_param('id', PARAM_INT);    // Course Module ID
 
if (!$cm = get_coursemodule_from_id('coursework', $id)) {
    print_error('Course Module ID was incorrect'); // NOTE this is invalid use of print_error, must be a lang string id
}
if (!$course = $DB->get_record('course', array('id'=> $cm->course))) {
    print_error('course is misconfigured');  // NOTE As above
}

$url = new moodle_url(deleteStudentCoursework\Main::MODULE_URL, array('id'=>$id));
$PAGE->set_url($url);

$PAGE->set_context(context_module::instance($cm->id));
$PAGE->set_pagelayout('incourse');
$PAGE->set_cm($cm);

$PAGE->set_title(get_string('delete_student_coursework', 'coursework'));
$PAGE->set_heading(get_string('delete_student_coursework', 'coursework'));

$PAGE->requires->css('/mod/coursework/css/common.css');
$PAGE->requires->css('/mod/coursework/css/support/delete_student_coursework.css');
$PAGE->requires->js('/mod/coursework/js/support/delete_student_coursework.js');

require_login();

echo $OUTPUT->header();

$deleteStudentCoursework = new deleteStudentCoursework\Main($course, $cm);
echo $deleteStudentCoursework->get_page();

echo $OUTPUT->footer();

