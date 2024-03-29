<?php

require_once '../../../config.php';
require_once '../classes/manage_old_files_area/main.php';
require_once '../lib/getters/common_getter.php';
require_once '../lib/getters/teachers_getter.php';
require_once '../lib/getters/user_getter.php';
require_once '../lib/enums.php';

use Coursework\View\ManageOldFilesArea\Main;
 
$id = required_param('id', PARAM_INT);    // Course Module ID
 
if (!$cm = get_coursemodule_from_id('coursework', $id)) {
    print_error('Course Module ID was incorrect'); // NOTE this is invalid use of print_error, must be a lang string id
}
if (!$course = $DB->get_record('course', array('id'=> $cm->course))) {
    print_error('course is misconfigured');  // NOTE As above
}

$url = new moodle_url("/mod/coursework/pages/manage_old_files_area.php", array('id'=>$id));
$PAGE->set_url($url);

$PAGE->set_context(context_module::instance($cm->id));
$PAGE->set_pagelayout('incourse');
$PAGE->set_cm($cm);

$PAGE->set_title(get_string('manage_old_files_area', 'coursework'));
$PAGE->set_heading(get_string('manage_old_files_area', 'coursework'));

$PAGE->requires->js('/mod/coursework/js/view.js');

require_login();
  
echo $OUTPUT->header();

$manageOldFilesArea = new Main($cm);
echo $manageOldFilesArea->get_page();

echo $OUTPUT->footer();


