<?php

require_once '../../../../config.php';
require_once '../../lib/getters/teachers_getter.php';
require_once '../../lib/getters/courses_getter.php';
require_once '../../lib/getters/user_getter.php';
require_once '../../lib/step_by_step.php';
require_once '../../classes/config/appoint_leaders/main.php';
require_once '../../lib/feedbacker.php';
require_once '../../lib/enums.php';

use Coursework\Config\AppointLeaders as appointLeaders;
 
$id = required_param('id', PARAM_INT);    // Course Module ID
 
if (!$cm = get_coursemodule_from_id('coursework', $id)) {
    print_error('Course Module ID was incorrect'); // NOTE this is invalid use of print_error, must be a lang string id
}
if (!$course = $DB->get_record('course', array('id'=> $cm->course))) {
    print_error('course is misconfigured');  // NOTE As above
}

$url = new moodle_url(appointLeaders\Main::MODULE_URL, array('id'=>$id));
$PAGE->set_url($url);

$PAGE->set_context(context_module::instance($cm->id));
$PAGE->set_pagelayout('incourse');
$PAGE->set_cm($cm);

$PAGE->set_title(get_string('appoint_leaders', 'coursework'));
$PAGE->set_heading(get_string('appoint_leaders', 'coursework'));

$PAGE->requires->css('/mod/coursework/css/common.css');
$PAGE->requires->css('/mod/coursework/css/config/appoint_leaders.css');
$PAGE->requires->css('/mod/coursework/css/external/introjs.min.css?v=1');
$PAGE->requires->js('/mod/coursework/js/external/intro.min.js', true);
$PAGE->requires->js('/mod/coursework/js/config/appoint_leaders.js');

require_login();

echo $OUTPUT->header();

$module = new appointLeaders\Main($course, $cm);
echo $module->get_page();

echo $OUTPUT->footer();

