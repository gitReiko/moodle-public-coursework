<?php

require_once('../../config.php');
require_once 'newlib.php';
 
$id = required_param('id', PARAM_INT);           // Course ID
 
// Ensure that the course specified is valid
if (!$course = $DB->get_record('course', array('id'=> $id))) 
{
    print_error('Course ID is incorrect');
}

$url = new moodle_url("/mod/coursework/index.php");
$PAGE->set_url($url);

$PAGE->set_context(context_course::instance($course->id));
$PAGE->set_pagelayout('incourse');
$PAGE->set_course($course);

$PAGE->set_title(get_string('pluginname', 'coursework'));
$PAGE->set_heading(get_string('pluginname', 'coursework'));

$PAGE->requires->css('/mod/coursework/css/index.css');

require_login();
  
echo $OUTPUT->header();

$courseworks =  cw_get_course_courseworks($course->id);
echo cw_get_courseworks_table($courseworks);

echo $OUTPUT->footer();



function cw_get_course_courseworks($course) : array 
{
    global $DB;
    return $DB->get_records('coursework', array('course'=>$course), '', 'id, name');
}

function cw_get_coursework_module_id() : int 
{
    global $DB;
    $record = $DB->get_record('modules', array('name'=>'coursework'));

    if(isset($record->id)) return $record->id;
    else return 0;
}

function cw_get_courseworks_table($courseworks) : string 
{
    $module = cw_get_coursework_module_id();

    $str = '<table class="index">';

    foreach($courseworks as $coursework)
    {
        $courseModule = cw_get_course_modules($module, $coursework);
        if($courseModule)
        {
            $str.= '<tr>';
            $str.= '<td>'.cw_get_section_name($courseModule).'</td>';
            $str.= '<td>'.cw_get_coursework_link($courseModule, $coursework).'</td>';
            $str.= '</tr>';
        }
    }

    $str.= '</table>';

    return $str;
}

function cw_get_course_modules($module, $coursework) 
{
    global $DB;
    $conditions = array('module'=>$module, 'instance'=>$coursework->id, 'deletioninprogress'=>0);
    return $DB->get_record('course_modules', $conditions);
}

function cw_get_section_name($module) : string 
{
    global $DB;
    $record = $DB->get_record('course_sections', array('id'=>$module->section));

    $str = '';
    if(isset($record->name)) $str .= $record->name;

    return $str;
}

function cw_get_coursework_link($module, $coursework) : string 
{
    $str = '<a href="/mod/coursework/view.php?id='.$module->id.'" >';
    $str.= $coursework->name.'</a>';
    return $str;
}










