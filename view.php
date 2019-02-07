<?php

require_once '../../config.php';
require_once 'classes/view/coursework_view.php';
require_once 'classes/view/student_view.php';
require_once 'classes/view/tutor_view.php';
require_once 'classes/view/manager_view.php';
require_once 'classes/view/database_events_handler.php';
require_once 'enums.php';
require_once 'lib.php';
 
$id = required_param('id', PARAM_INT);    // Course Module ID
 
if (!$cm = get_coursemodule_from_id('coursework', $id)) {
    print_error('Course Module ID was incorrect'); // NOTE this is invalid use of print_error, must be a lang string id
}
if (!$course = $DB->get_record('course', array('id'=> $cm->course))) {
    print_error('course is misconfigured');  // NOTE As above
}

$url = new moodle_url("/mod/coursework/view.php", array('id'=>$id));
$PAGE->set_url($url);

$PAGE->set_context(context_module::instance($cm->id));
$PAGE->set_pagelayout('incourse');
$PAGE->set_cm($cm);

$PAGE->set_title(get_string('pluginname', 'coursework'));
$PAGE->set_heading(get_string('pluginname', 'coursework'));

$PAGE->requires->css('/mod/coursework/css/view.css');
$PAGE->requires->js('/mod/coursework/js/view.js');

require_login();
  
echo $OUTPUT->header();

if(has_capability('mod/coursework:removeselection', $PAGE->cm->context))
{
    $coursework = new ManagerCourseworkView($course, $cm);
    $coursework->display();
}
else if(has_capability('mod/coursework:gradestudent', $PAGE->cm->context))
{
    $coursework = new TutorCourseworkView($course, $cm);
    $coursework->display();
}
else if(has_capability('mod/coursework:selecttutor', $PAGE->cm->context))
{
    $coursework = new StudentCourseworkView($course, $cm);
    $coursework->display();
}

echo $OUTPUT->footer();


