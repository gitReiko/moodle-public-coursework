<?php

// General strings
$string['pluginname'] = 'Course work';
$string['modulename'] = 'Course work';
$string['modulenameplural'] = 'Course works';
$string['name'] = 'Name';
$string['intro'] = 'Introduction';
$string['pluginadministration'] = 'Course work administration';

// Coursework configuration
$string['coursework_configuration'] = 'Coursework configuration';
$string['participants_management'] = 'Participants management';
$string['theme'] = 'Theme';
$string['themes_management'] = 'Themes management';
$string['no_available_themes'] = 'No available themes';
$string['use_own_theme'] = 'Use own theme';

// Enrollment strings
$string['configurate_coursework'] = 'Configurate coursework';
$string['select_groups'] = 'Select groups whose students will participate in this course work';
$string['quota_left'] = 'Need to allocate quotas: ';
$string['add_tutor'] = 'Add tutor';
$string['no_permission'] = 'You don\'t have permission to view this page';
$string['save_changes'] = 'Save changes';
$string['delete'] = 'Delete';

// Themes management
$string['coursework_themes_management'] = 'Coursework themes management';
$string['add_new_theme'] = 'Add new theme';
$string['edit'] = 'Edit';

// Students assignment
$string['students_assignment'] = 'Students assignment';
$string['students_assignment_header'] = 'Students assignment by coursework items';
$string['group_assignment'] = 'Group assignment';
$string['no_assign'] = 'No assign';

// View strings
$string['fullname'] = 'Fullname';
$string['group'] = 'Group';
$string['leader'] = 'Leader';
$string['course'] = 'Course';
$string['grade'] = 'Preliminary grade';
$string['comment'] = 'Comment';
$string['make_choice'] = 'Make a choice';
$string['grade_student'] = 'Grade student';
$string['remove_selection'] = 'Remove selection';
$string['cant_be_undone'] = 'The choice made cannot be canceled by yourself.';
$string['back_to_course'] = 'Back to course';
$string['not_selected'] = 'Not selected';
$string['no_leaders'] = 'No available leaders';

// Errors strings
$string['e:missing-coursework-student-record'] = 'Error: missing record of coursework student.';
$string['e:missing-user-record'] = 'Error: missing record of user.';
$string['e:missing-student-record-id'] = 'Error: missing row id of deleting student.';
$string['e:missing-student-id'] = 'Error: missing id of deleting student. Mail notification will not be sent.';
$string['e:missing-grade-and-comment'] = 'Error: missing new grade and new comment.';
$string['e:student-not-deleted'] = 'Database error: coursework student record not deleted.';
$string['e:student-not-updated'] = 'Database error: coursework student record not updated.';

$string['error_no_tutor_or_course'] = 'Error: missing tutor id and/or course id.';
$string['error_missing_group_id'] = 'Error: missing group id.';
$string['error_no_tutor_necessary_data'] = 'Error: missing tutor id and/or course id and/or quota.';
$string['error_theme_already_used'] = 'Error: This theme is already being used by another student.';
$string['error_tutor_quota_over'] = 'Error: The quota for the selected buch teacher + course is over.';
$string['error_student_already_chosen_theme'] = 'Error: Student {$a} has already chosen the subject of his coursework.';
$string['error_tutor_total_quota_over'] = 'Error: Tutor {$a->tutor} quota is exhausted. Student {$a->student} isnt assigned coursework.';

// Messages strings
$string['messageprovider:tutorselected'] = 'Message about choosing you as leader of the course work.';
$string['tutorselected:head'] = 'Student has chosen you as leader of the course work';
$string['messageprovider:studentgraded'] = 'Message about the preliminary grade or comment of course work.';
$string['studentgraded:head'] = 'Changed preliminary grade or comment of the course work';
$string['messageprovider:selectionremoved'] = 'Message about the cancellation of your choice of course work.';
$string['selectionremoved:head'] = 'Course work leader selection removed';
$string['selectionremoved:body'] = 'The choice you made earlier in your course work has been removed along with all the progress made. Choose a new leader and continue working with him.';


$string['coursework_link1'] = 'Course work ';
$string['coursework_link2'] = 'available on site.';


// New view message strings
$string['student_message'] = '<p>User {$a->tutor} {$a->date} at {$a->time} chose you as leader of the course work.</p>';
$string['tutor_message'] = '<p>User {$a->tutor} {$a->date} at {$a->time} pre-grade and/or comment your course work.</p>';
$string['manager_message'] = '<p>User {$a->tutor} {$a->date} at {$a->time} removed your choice of course work leader.</p><p>All received progress has been deleted. To continue work, select a new leader.</p>';
$string['answer_not_require'] = '<p>*This message was sent automatically and does not require a response.</p>';
$string['grade_isnt_final'] = '<p>*Preliminary grade is not final.</p>';






