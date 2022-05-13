<?php



defined('MOODLE_INTERNAL') || die();

$messageproviders = array(
    // Notify that the student select their theme
    'selecttheme' => array(
        'capability' => 'mod/coursework:selecttheme'
    ),

    'givetask' => array(
        'capability' => 'mod/coursework:givetask'
    ),

    'sendsectionforcheck' => array(
        'capability' => 'mod/coursework:sendsectionforcheck'
    ),

    'sendworkforcheck' => array(
        'capability' => 'mod/coursework:sendsectionforcheck'
    ),

    'sectioncheck' => array(
        'capability' => 'mod/coursework:gradestudent'
    ),

    'workcheck' => array(
        'capability' => 'mod/coursework:gradestudent'
    ),

    'chatmessage' => array(
        'capability' => 'mod/coursework:gradestudent'
    ),

    'taskassignment' => array(
        'capability' => 'mod/coursework:taskassignment'
    ),

    // Notify that the leader grade their student
    'studentgraded' => array(
        'capability' => 'mod/coursework:gradestudent'
    ),

    // Notify that student selection was removed
    'leader_changed' => array(
        'capability' => 'mod/coursework:is_student'
    ),

    'student_upload_file' => array(
        'capability' => 'mod/coursework:is_student'
    ),

    'teacher_upload_file' => array(
        'capability' => 'mod/coursework:is_teacher'
    ),

    'return_work_for_rework' => array(
        'capability' => 'mod/coursework:is_student'
    ),

    'leaderreplaced' => array(
        'capability' => 'mod/coursework:leaderreplacement'
    ),

    'studentworkdeleted' => array(
        'capability' => 'mod/coursework:deletestudentcoursework'
    ),

    'themeselectiondeleted' => array(
        'capability' => 'mod/coursework:returntothemeselection'
    ),

);






