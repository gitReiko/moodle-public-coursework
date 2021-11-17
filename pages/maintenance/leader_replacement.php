<?php

require_once '../../../../config.php';
//require_once '../classes/config/leader_change/main.php';
require_once '../../enums.php';
require_once '../../newlib.php';

//use Coursework\Config\LeadersSetting as leadersSetting;
 
$id = required_param('id', PARAM_INT);    // Course Module ID
 
if (!$cm = get_coursemodule_from_id('coursework', $id)) {
    print_error('Course Module ID was incorrect'); // NOTE this is invalid use of print_error, must be a lang string id
}
if (!$course = $DB->get_record('course', array('id'=> $cm->course))) {
    print_error('course is misconfigured');  // NOTE As above
}

$url = new moodle_url('/mod/coursework/pages/maintenance/leader_replacement.php', array('id'=>$id));
$PAGE->set_url($url);

$PAGE->set_context(context_module::instance($cm->id));
$PAGE->set_pagelayout('incourse');
$PAGE->set_cm($cm);

$PAGE->set_title(get_string('leader_replacement', 'coursework'));
$PAGE->set_heading(get_string('leader_replacement', 'coursework'));

//$PAGE->requires->css('/mod/coursework/css/config/leaders_settings.css');

require_login();
  
echo $OUTPUT->header();

//$leadersSetting = new leadersSetting\Main($course, $cm);
//echo $leadersSetting->execute();

echo $OUTPUT->footer();

