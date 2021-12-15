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

$url = new moodle_url('/mod/coursework/pages/support/list.php', array('id'=>$id));
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

$attr = array('href' => '/mod/coursework/pages/support/return_to_theme_selection.php?id='.$id);
$text = \html_writer::tag('p', get_string('return_to_theme_selection', 'coursework'));
echo \html_writer::tag('a', $text, $attr);

$attr = array('href' => '/mod/coursework/pages/support/back_to_work_state.php?id='.$id);
$text = \html_writer::tag('p', get_string('back_to_work_state', 'coursework'));
echo \html_writer::tag('a', $text, $attr);

$attr = array('href' => '/mod/coursework/pages/support/leader_replacement.php?id='.$id);
$text = \html_writer::tag('p', get_string('leader_replacement', 'coursework'));
echo \html_writer::tag('a', $text, $attr);

$attr = array('href' => '/mod/coursework/pages/support/delete_student_coursework.php?id='.$id);
$text = \html_writer::tag('p', get_string('delete_student_coursework', 'coursework'));
echo \html_writer::tag('a', $text, $attr);


echo $OUTPUT->footer();

