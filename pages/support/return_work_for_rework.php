<?php

require_once '../../../../config.php';
require_once '../../classes/support/return_work_for_rework/main.php';
require_once '../../lib/getters/students_getter.php';
require_once '../../lib/getters/common_getter.php';
require_once '../../lib/notification.php'; 
require_once '../../lib/common.php';
require_once '../../lib/enums.php';

$id = required_param('id', PARAM_INT);    // Course Module ID
 
if (!$cm = get_coursemodule_from_id('coursework', $id)) {
    print_error('Course Module ID was incorrect'); // NOTE this is invalid use of print_error, must be a lang string id
}
if (!$course = $DB->get_record('course', array('id'=> $cm->course))) {
    print_error('course is misconfigured');  // NOTE As above
}

$url = new moodle_url("/mod/coursework/pages/support/return_work_for_rework.php", array('id'=>$id));
$PAGE->set_url($url);

$PAGE->set_context(context_module::instance($cm->id));
$PAGE->set_pagelayout('incourse');
$PAGE->set_cm($cm);

$PAGE->set_title(get_string('return_work_for_rework', 'coursework'));
$PAGE->set_heading(get_string('return_work_for_rework', 'coursework'));

$PAGE->requires->css('/mod/coursework/css/common.css');

require_login();
  
echo $OUTPUT->header();

$page = new Coursework\Support\BackToWorkState\Main($cm, $course);
echo $page->get_page();

echo $OUTPUT->footer();


