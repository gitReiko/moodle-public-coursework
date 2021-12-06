<?php

require_once '../../../../config.php';
require_once '../../classes/config/distribute_to_leaders/main.php';
require_once '../../lib/getters/students_getter.php';
require_once '../../lib/getters/teachers_getter.php';
require_once '../../lib/getters/common_getter.php';
require_once '../../lib/notification.php'; 
require_once '../../lib/common.php';
require_once '../../lib/enums.php';

use Coursework\Support\LeaderReplacement as leaderReplacement;
 
$id = required_param('id', PARAM_INT);    // Course Module ID
 
if (!$cm = get_coursemodule_from_id('coursework', $id)) {
    print_error('Course Module ID was incorrect'); // NOTE this is invalid use of print_error, must be a lang string id
}
if (!$course = $DB->get_record('course', array('id'=> $cm->course))) {
    print_error('course is misconfigured');  // NOTE As above
}

$url = new moodle_url('/mod/coursework/pages/config/distribute_to_leaders.php', array('id'=>$id));
$PAGE->set_url($url);

$PAGE->set_context(context_module::instance($cm->id));
$PAGE->set_pagelayout('incourse');
$PAGE->set_cm($cm);

$PAGE->set_title(get_string('distribute_to_leaders', 'coursework'));
$PAGE->set_heading(get_string('distribute_to_leaders', 'coursework'));

$PAGE->requires->css('/mod/coursework/css/lib/students_mass_actions.css');
$PAGE->requires->js('/mod/coursework/js/lib/mass_actions_on_students.js');

require_login();
  
echo $OUTPUT->header();

echo 'dsvdsvdsdsvs';
/*
$leaderReplacement = new leaderReplacement\Main($course, $cm);
echo $leaderReplacement->execute();
*/

echo $OUTPUT->footer();

