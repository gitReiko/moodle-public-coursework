<?php

require_once '../../config.php';
require_once 'classes/view/main.php';
require_once 'enums.php';
require_once 'lib.php';

require_once 'lib/getters/student_task_getter.php';
require_once 'lib/getters/students_getter.php';
require_once 'lib/getters/teachers_getter.php';
require_once 'lib/getters/common_getter.php';
require_once 'lib/teacher_notifications.php';
require_once 'lib/notification.php';
require_once 'lib/common.php';
require_once 'lib/enums.php';

use Coursework\View\Main;
 
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

$PAGE->requires->css('/mod/coursework/css/view.css?v=1');
$PAGE->requires->css('/mod/coursework/css/student_work.css?v=1.0.28');
$PAGE->requires->css('/mod/coursework/css/students_works.css?v=1.1.1');
$PAGE->requires->js('/mod/coursework/js/view.js');
$PAGE->requires->js('/mod/coursework/js/chat.js');
$PAGE->requires->js('/mod/coursework/js/select_theme.js');

require_login();

$view = new Main($course, $cm);
  
$view->handle_database_event();

echo $OUTPUT->header();
echo $view->get_gui();
echo $OUTPUT->footer();


