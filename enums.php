<?php

// Post constants
const ECM_COURSEWORK = 'ecm_coursework';
const ECM_SELECT_TUTOR = 'ecm_select_tutor';
const ECM_SELECT_COURSE = 'ecm_select_course';
const ECM_STUDENTS = 'ecm_students';
const ECM_GRADE = 'ecm_grade';
const ECM_COMMENT = 'ecm_comment';
const ECM_GRADE_STUDENT = 'ecm_grade_student';
const ECM_REMOVE_SELECTION = 'ecm_remove_selection';
const ECM_THEME_NAME = 'ecm_theme_name';

const DB_EVENT = 'database_event';

// Coursework configuration modules
const CONFIG_MODULE = 'config_module';
const PARTICIPANTS_MANAGEMENT = 'participants_management';
const THEMES_MANAGEMENT = 'themes_management';
const STUDENTS_ASSIGNMENT = 'students_assignment';
const CONFIG_MODULES = array(PARTICIPANTS_MANAGEMENT, THEMES_MANAGEMENT, STUDENTS_ASSIGNMENT);

// Types of database events
const ADD = 'add';
const EDIT = 'edit';
const DEL = 'delete';
const SELECT = 'select';

// Type of database abstractions
const THEME = 'theme';
const NAME = 'name';
const COURSE = 'course';
const COURSES = 'courses';
const ID = 'id';
const GROUP = 'group';
const GROUPS = 'groups';
const PERSONAL = 'personal';
const OWN_THEME = 'own_theme';
const TUTOR = 'tutor';
const TUTORS = 'tutors';
const COURSEWORK = 'coursework';
const ASSIGNMENT = 'assignment';
const QUOTAS = 'quotas';

// Role constants
const MANAGER_ROLE = 1;
const EDITING_TEACHER_ROLE = 3;
const TEACHER_ROLE = 4;
const STUDENT_ROLE = 5;
const GUEST_ROLE = 6;
const TUTOR_ROLE = 9;

// Forms
const STUDENT_FORM = 'student_form';
const TUTOR_FORM = 'tutor_form_';
const MANAGER_FORM = 'manager_form_';

// Other
const NO_ASSIGN = 'no_assign';
