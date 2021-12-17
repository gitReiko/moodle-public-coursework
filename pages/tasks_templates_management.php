<?php

require_once '../../../config.php';
require_once '../classes/tasks_templates_management/main.php';

use Coursework\View\TasksTemplatesManagement\Main as tasksTemplatesManagement;
 
$id = required_param('id', PARAM_INT);    // Course Module ID
 
if (!$cm = get_coursemodule_from_id('coursework', $id)) {
    print_error('Course Module ID was incorrect'); // NOTE this is invalid use of print_error, must be a lang string id
}
if (!$course = $DB->get_record('course', array('id'=> $cm->course))) {
    print_error('course is misconfigured');  // NOTE As above
}

$url = new moodle_url('/mod/coursework/pages/tasks_templates_management.php', array('id'=>$id));
$PAGE->set_url($url);

$PAGE->set_context(context_module::instance($cm->id));
$PAGE->set_pagelayout('incourse');
$PAGE->set_cm($cm);

$PAGE->set_title(get_string('tasks_templates_management', 'coursework'));
$PAGE->set_heading(get_string('tasks_templates_management', 'coursework'));

require_login();

$tasksTemplatesManagement = new tasksTemplatesManagement($course, $cm);
  
$tasksTemplatesManagement->handle_database_event();

echo $OUTPUT->header();
echo $tasksTemplatesManagement->get_page();
echo $OUTPUT->footer();

