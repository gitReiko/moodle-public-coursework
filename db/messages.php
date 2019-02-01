<?php



defined('MOODLE_INTERNAL') || die();

$messageproviders = array(
    // Notify that the student chose their leader
    'tutorselected' => array(
        'capability' => 'mod/coursework:selecttutor'
    ),

    // Notify that the leader grade their student
    'studentgraded' => array(
        'capability' => 'mod/coursework:gradestudent'
    ),

    // Notify that student selection was removed
    'selectionremoved' => array(
        'capability' => 'mod/coursework:removeselection'
    ),
);






