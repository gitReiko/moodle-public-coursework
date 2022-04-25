<?php

require_once '../../../config.php';
require_once '../classes/themes_collections_management/main.php';
require_once '../lib/getters/common_getter.php';
require_once '../lib/feedbacker.php';
require_once '../lib/enums.php';

use Coursework\View\ThemesCollectionsManagement\Main as themesCollectionsManagement;
 
$id = required_param('id', PARAM_INT);    // Course Module ID
 
if (!$cm = get_coursemodule_from_id('coursework', $id)) {
    print_error('Course Module ID was incorrect'); // NOTE this is invalid use of print_error, must be a lang string id
}
if (!$course = $DB->get_record('course', array('id'=> $cm->course))) {
    print_error('course is misconfigured');  // NOTE As above
}

$url = new moodle_url('/mod/coursework/pages/themes_collections_management.php', array('id'=>$id));
$PAGE->set_url($url);

$PAGE->set_context(context_module::instance($cm->id));
$PAGE->set_pagelayout('incourse');
$PAGE->set_cm($cm);

$PAGE->set_title(get_string('themes_collections_management', 'coursework'));
$PAGE->set_heading(get_string('themes_collections_management', 'coursework'));

$PAGE->requires->js('/mod/coursework/js/themes_collections_management.js');

require_login();

echo $OUTPUT->header();

$module = new themesCollectionsManagement($course, $cm);
echo $module->get_page();

echo $OUTPUT->footer();

