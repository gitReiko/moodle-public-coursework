<?php

namespace Coursework\Lib\Getters;

use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\Enums as enum;

class StudentsGetter
{

    public static function get_all_students(\stdClass $cm)
    {
        $groupMode = cg::get_coursework_group_mode($cm);

        if($groupMode == enum::NO_GROUPS)
        {
            return self::get_all_course_students($cm);
        }
        else 
        {
            return self::get_students_from_available_groups($cm);
        }
    }

    public static function get_all_course_students(\stdClass $cm)
    {
        $context = \context_module::instance($cm->id); 
        $groupId = enum::NO_GROUPS;
        $userfields = 'u.id,u.firstname,u.lastname,u.email,u.phone1,u.phone2';
        $orderby = 'u.lastname';

        return get_enrolled_users(
            $context, 
            'mod/coursework:is_student',
            $groupId, 
            $userfields, 
            $orderby
        );
    }

    public static function get_students_from_available_groups(\stdClass $cm)
    {
        $students = array();

        $groups = cg::get_coursework_groups($cm);

        foreach($groups as $group)
        {
            $students = array_merge(
                $students,
                self::get_students_from_group($cm, $group->id)
            );
        }

        $students = self::get_unique_students($students);

        usort($students, function($a, $b)
        {
            return strcmp(
                $a->lastname.$a->firstname, 
                $b->lastname.$b->firstname);
        });

        return $students;
    }

    public static function get_students_from_group(\stdClass $cm, int $groupId)
    {
        $context = \context_module::instance($cm->id); 
        $userfields = 'u.id,u.firstname,u.lastname,u.email,u.phone1,u.phone2';
        $orderby = 'u.lastname';

        return get_enrolled_users(
            $context, 
            'mod/coursework:is_student',
            $groupId, 
            $userfields, 
            $orderby
        );
    }

    public static function get_all_coursework_students_works(int $courseworkId) 
    {
        global $DB;
        $where = array('coursework' => $courseworkId);
        return $DB->get_records('coursework_students', $where);
    }

    public static function add_works_to_students(int $courseworkId, $students)
    {
        foreach($students as $student)
        {
            $work = self::get_students_work($courseworkId, $student->id);

            if($work)
            {
                $student = self::add_works_params_to_student($student, $work);
            }
            else 
            {
                $student = self::add_empty_student_work_params($student);
            }

        }

        return $students;
    }

    public static function get_students_work(int $courseworkId, int $studentId) 
    {
        global $DB;
        $where = array(
            'coursework' => $courseworkId,
            'student' => $studentId
        );
        return $DB->get_record('coursework_students', $where);
    }

    public static function get_student_theme(\stdClass $work)
    {
        if(!empty($work->theme))
        {
            return cg::get_coursework_theme_name($work->theme);
        }
        else if(!empty($work->owntheme))
        {
            return $work->owntheme;
        }
        else 
        {
            return '';
        }
    }

    private static function get_unique_students($students)
    {
        $unique = array();

        foreach($students as $student)
        {
            if(self::is_student_not_exist_in_array($unique, $student))
            {
                $unique[] = $student;
            }
        }

        return $unique;
    }

    private static function is_student_not_exist_in_array($unique, $student) : bool 
    {
        foreach($unique as $uStudent)
        {
            if($uStudent->id == $student->id)
            {
                return false;
            }
        }

        return true;
    }

    private static function add_empty_student_work_params(\stdClass $student)
    {
        $student->teacher = '';
        $student->course = '';
        $student->theme = '';
        $student->grade = '';
        $student->task = '';
        $student->latestStatus = '';
        $student->statusChangeTime = '';

        return $student;
    }

    private static function add_works_params_to_student(\stdClass $student, \stdClass $work)
    {
        $student->teacher = $work->teacher;
        $student->course = $work->course;
        $student->theme = self::get_student_theme($work);
        $student->grade = $work->grade;
        $student->task = $work->task;

        $lastState =  self::get_coursework_student_latest_state($student, $work);
        $student->latestStatus = $lastState->status;
        $student->statusChangeTime = $lastState->changetime;

        return $student;
    }

    private static function get_coursework_student_latest_state(\stdClass $student, \stdClass $work)
    {
        global $DB;
        $sql = 'SELECT `status`, changetime
                FROM {coursework_students_statuses}
                WHERE type = `coursework` 
                AND instance = ?
                AND student = ?
                GROUP BY student
                HAVING changetime = MAX(changetime)';
        $params = array($work->coursework, $student->id);
        return $DB->get_record_sql($sql, $params);
    }


}

