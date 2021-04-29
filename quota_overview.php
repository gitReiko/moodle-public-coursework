<?php

require_once '../../config.php';
require_once 'classes/view/quota_overview/main.php';
require_once 'lib/getters/common_getter.php';
require_once 'lib/getters/students_getter.php';
require_once 'lib/getters/teachers_getter.php';
require_once 'lib/enums.php';
require_once 'newlib.php';
 
$id = required_param('id', PARAM_INT);    // Course Module ID
 
if (!$cm = get_coursemodule_from_id('coursework', $id)) {
    print_error('Course Module ID was incorrect'); // NOTE this is invalid use of print_error, must be a lang string id
}
if (!$course = $DB->get_record('course', array('id'=> $cm->course))) {
    print_error('course is misconfigured');  // NOTE As above
}

$url = new moodle_url("/mod/coursework/quota_overview.php", array('id'=>$id));
$PAGE->set_url($url);

$PAGE->set_context(context_module::instance($cm->id));
$PAGE->set_pagelayout('incourse');
$PAGE->set_cm($cm);

$PAGE->set_title(get_string('quota_overview', 'coursework'));
$PAGE->set_heading(get_string('quota_overview', 'coursework'));

$PAGE->requires->css('/mod/coursework/css/overview_quota.css');

require_login();
  
echo $OUTPUT->header();

$page = new Coursework\View\QuotaOverview\Main($cm);
echo $page->get_page();

echo $OUTPUT->footer();

