<?php

require_once '../../../../config.php';
require_once '../../classes/config/set_default_task_template/main.php';
require_once '../../lib/getters/common_getter.php';

use Coursework\Config\SetDefaultTaskTemplate\Main as setUsedTaskTemplate;
 
$id = required_param('id', PARAM_INT); // Course Module ID
 
if (!$cm = get_coursemodule_from_id('coursework', $id)) {
    print_error('Course Module ID was incorrect'); // NOTE this is invalid use of print_error, must be a lang string id
}
if (!$course = $DB->get_record('course', array('id'=> $cm->course))) {
    print_error('course is misconfigured'); // NOTE As above
}

$url = new moodle_url('/mod/coursework/pages/config/set_default_task_template.php', array('id'=>$id));
$PAGE->set_url($url);

$PAGE->set_context(context_module::instance($cm->id));
$PAGE->set_pagelayout('incourse');
$PAGE->set_cm($cm);

$PAGE->set_title(get_string('set_default_task_template', 'coursework'));
$PAGE->set_heading(get_string('set_default_task_template', 'coursework'));

$PAGE->requires->css('/mod/coursework/css/config/set_default_task_template.css');

require_login();

$setUsedTaskTemplate = new setUsedTaskTemplate($course, $cm);
  
$setUsedTaskTemplate->handle_database_event();

echo $OUTPUT->header();
echo $setUsedTaskTemplate->get_page();
echo $OUTPUT->footer();

