<?php

// General strings
$string['pluginname'] = 'Course work';
$string['modulename'] = 'Course work';
$string['modulenameplural'] = 'Course works';
$string['name'] = 'Name';
$string['intro'] = 'Introduction';
$string['pluginadministration'] = 'Course work administration';
$string['change'] = 'Change';
$string['description'] = 'Description';

// Coursework configuration
$string['coursework_configuration'] = 'Coursework configuration';
$string['leaders_setting'] = 'Leaders setting';
$string['theme'] = 'Theme';
$string['themes_management'] = 'Themes management';
$string['no_available_themes'] = 'No available themes';
$string['use_own_theme'] = 'Use own theme';

// Leaders setting
$string['leaders_overview_table_header'] = 'List of coursework leaders';
$string['add_leader_header'] = 'Adding a new leader';
$string['edit_leader_header'] = 'Leader Editing';
$string['quota_title'] = 'The quota sets the number of students with whom the leader will work.';
$string['add_leader'] = 'Add leader';
$string['back'] = 'Back';
$string['configurate_coursework'] = 'Configurate coursework';
$string['add_teacher'] = 'Add teacher';
$string['no_permission'] = 'You don\'t have permission to view this page';
$string['save_changes'] = 'Save changes';
$string['delete'] = 'Delete';

// Themes management
$string['coursework_themes_management'] = 'Coursework themes management';
$string['add_new_theme'] = 'Add new theme';
$string['edit'] = 'Edit';

// Students distribution - sd
$string['students_distribution'] = 'Students distribution';
$string['sd_overview_header'] = 'Select students to distribute';
$string['select'] = 'Select: ';
$string['all_participants'] = 'All participants';
$string['cancel_choice'] = 'Cancel choice';
$string['distribute'] = 'Distribute';
$string['distribute_student_header'] = 'Distribute student';
$string['distribute_students_header'] = 'Distribute next students:';
$string['quota_exceeded'] = 'Count of students exceeds the quota of the leader.';
$string['expand_quota'] = 'Increase leaders quota to distribute all students.';
$string['dont_change_quota'] = 'Dont change quota (distribute part of the students).';
$string['student_redistribution_impossible'] = 'Re-distribution of student {$a->fullname} is impossible.';
$string['student_successfully_distributed'] = 'Student {$a->fullname} successfully distributed.';
$string['not_enough_quota_for_distribution'] = 'Not enough quota for student {$a->fullname} no_one_has_chosen_you_as_leader. Work with this activity will be possible after at least one student has chosen you as leader.';

// Remove distribution
$string['remove_distribution'] = 'Remove distribution';
$string['no_distributed_students'] = 'No one student is distributed, therefore it is impossible to remove the distribution.';
$string['remove_distribution_header'] = 'Remove students distribution';
$string['student'] = 'Student';

// Leader change
$string['leader_change'] = 'Leader change';
$string['lc_overview_header'] = 'Select students who need to change their leader';
$string['change_leader_for_selected_students'] = 'Change leader for selected students';
$string['change_leader_for_students_header'] = 'Change leader for students:';

// Collections management
$string['collections_management'] = 'Collections management';
$string['collections_list'] = 'Collections list';
$string['add_collection'] = 'Add collection';


// View strings
$string['fullname'] = 'Fullname';
$string['group'] = 'Group';
$string['leader'] = 'Leader';
$string['course'] = 'Course';
$string['quota'] = 'Quota';
$string['grade'] = 'Preliminary grade';
$string['comment'] = 'Comment';
$string['choose'] = 'Choose';
$string['grade_student'] = 'Grade student';
$string['remove_selection'] = 'Remove selection';
$string['cant_be_undone'] = 'The choice made cannot be canceled by yourself.';
$string['back_to_course'] = 'Back to course';
$string['not_selected'] = 'Not selected';
$string['not_available'] = 'Not available';

// Errors strings
$string['e:missing-coursework-student-record'] = 'Error: missing record of coursework student.';
$string['e:missing-user-record'] = 'Error: missing record of user.';
$string['e:missing-student-record-id'] = 'Error: missing row id of deleting student.';
$string['e:missing-student-id'] = 'Error: missing id of deleting student. Mail notification will not be sent.';
$string['e:missing-grade-and-comment'] = 'Error: missing new grade and new comment.';
$string['e:student-not-deleted'] = 'Database error: coursework student record not deleted.';
$string['e:student-not-updated'] = 'Database error: coursework student record not updated.';
$string['e:ins:student-not-selected'] = 'Database error: student record with selected theme not created.';
$string['e:upd:student-not-selected'] = 'Database error: student record with selected theme not updated.';
$string['e:missing-student-id'] = 'Error: missing student id.';
$string['e:missing-teacher-id'] = 'Error: missing teacher id.';
$string['e:missing-course-id'] = 'Error: missing course id.';
$string['e:missing-theme-and-owntheme'] = 'Error: missing theme id and own theme.';
$string['e:theme-already-used'] = 'Error: This theme is already being used by another student.';
$string['e:teacher-quota-over'] = 'Error: The quota for the selected buch teacher + course is over.';
$string['e:student-already-chosen-theme'] = 'Error: Student {$a} has already chosen the subject of his coursework.';
$string['e:teacher-total-quota-over'] = 'Error: Teacher {$a->teacher} quota is exhausted. Student {$a->student} isnt assigned coursework.';
$string['e:student_not_enrolled'] = 'Error: You are not enrolled in this coursework.';
$string['e:students_not_enrolled'] = 'Error: Students are not enrolled in this coursework.';
$string['e:teachers_not_enrolled'] = 'Error: Leaders are not enrolled in this coursework.';
$string['no_one_has_chosen_you_as_leader'] = 'At the moment, no one has chosen you as leader.';
$string['e-sv:quota_ended'] = 'There are no free course leaders left. Contact your administrator or teacher to resolve this issue.';

// new errors
// e - error
// le - leaders setting
// sd - students distribution
// ev - events handler
// tc - teacher change
$string['e-le-ev:missing_coursework'] = 'Missing param coursework id required for leaders_events_handler.';
$string['e-le-ev:missing_teacher'] = 'Missing param teacher id required for leaders_events_handler.';
$string['e-le-ev:missing_course'] = 'Missing param course id required for leaders_events_handler.';
$string['e-le-ev:missing_quota'] = 'Missing param students quota required for leaders_events_handler.';
$string['e-le-ev:missing_row_id'] = 'Missing param coursework_teachers id required for leaders_events_handler.';
$string['e-le-ev:leader_already_exist'] = 'At the same time there can be only one bundle of leader + course.';
$string['e-sd:no_leaders'] = 'The distribution of students by leaders is possible only after the determination of these leaders.';
$string['e-sd-ev:missing_leader_id'] = 'Missing required parameter leader id.';
$string['e-sd-ev:missing_course_id'] = 'Missing required parameter course id.';
$string['e-sd-ev:missing_leader_quota'] = 'Missing required parameter leader quota.';
$string['e-sd-ev:quota_isnt_numeric'] = 'Leader quota isnt integer.';
$string['e-tc:leader_not_changed'] = 'Coursework leader has not been changed.';

// Messages strings
$string['theme_selection_header'] = 'Student has chosen you as leader of the course work';
$string['student_graded_header'] = 'Changed preliminary grade or comment of the course work';
$string['selection_removed_header'] = 'The choice you made earlier in your course work has been removed along with all the progress made. Choose a new leader and continue working with him.';
$string['student_message'] = '<p>User {$a->teacher} {$a->date} at {$a->time} chose you as leader of the course work.</p>';
$string['teacher_message'] = '<p>User {$a->teacher} {$a->date} at {$a->time} pre-grade and/or comment your course work.</p>';
$string['manager_message'] = '<p>User {$a->teacher} {$a->date} at {$a->time} removed your choice of course work leader.</p><p>All received progress has been deleted. To continue work, select a new leader.</p>';
$string['answer_not_require'] = '<p>*This message was sent automatically and does not require a response.</p>';
$string['grade_isnt_final'] = '<p>*Preliminary grade is not final.</p>';
$string['coursework_link1'] = 'Course work ';
$string['coursework_link2'] = 'available on site.';

$string['leader_changed_for_student'] = 'Changed coursework leader';





