<?php

require_once '../../../../config.php';
require_once '../../newlib.php';
 
$id = required_param('id', PARAM_INT);    // Course Module ID
 
if (!$cm = get_coursemodule_from_id('coursework', $id)) {
    print_error('Course Module ID was incorrect'); // NOTE this is invalid use of print_error, must be a lang string id
}
if (!$course = $DB->get_record('course', array('id'=> $cm->course))) {
    print_error('course is misconfigured');  // NOTE As above
}

$url = new moodle_url('/mod/coursework/pages/config/list.php', array('id'=>$id));
$PAGE->set_url($url);

$PAGE->set_context(context_module::instance($cm->id));
$PAGE->set_pagelayout('incourse');
$PAGE->set_cm($cm);

$PAGE->set_title(get_string('config_list', 'coursework'));
$PAGE->set_heading(get_string('config_list', 'coursework'));

require_login();
  
echo $OUTPUT->header();

$text = get_string('config_list', 'coursework');
echo \html_writer::tag('h2', $text);

$attr = array('href' => '/mod/coursework/pages/config/appoint_leaders.php?id='.$id);
$text = \html_writer::tag('p', get_string('appoint_leaders', 'coursework'));
echo \html_writer::tag('a', $text, $attr);

$attr = array('href' => '/mod/coursework/pages/config/distribute_to_leaders.php?id='.$id);
$text = \html_writer::tag('p', get_string('distribute_to_leaders', 'coursework'));
echo \html_writer::tag('a', $text, $attr);

$attr = array('href' => '/mod/coursework/pages/config/set_default_task_template.php?id='.$id);
$text = \html_writer::tag('p', get_string('set_default_task_template', 'coursework'));
echo \html_writer::tag('a', $text, $attr);

$attr = array('href' => '/mod/coursework/pages/config/set_suggested_themes.php?id='.$id);
$text = \html_writer::tag('p', get_string('set_suggested_themes', 'coursework'));
echo \html_writer::tag('a', $text, $attr);

echo $OUTPUT->footer();

