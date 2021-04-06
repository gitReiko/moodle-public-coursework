<?php

namespace Coursework\Lib\Getters;

use Coursework\Lib\Getters\StudentsGetter as sg;
use Coursework\Lib\Enums as enum;

class TeachersGetter
{

    public static function get_only_configured_course_work_teachers(int $courseworkId)
    {
        global $DB;
        $sql = 'SELECT ct.id as courseworkteacherid, ct.teacher as teacherid, 
                    u.firstname, u.lastname, u.email, u.phone1, u.phone2, 
                    ct.course as courseid, c.fullname as coursename, ct.quota
                FROM {coursework_teachers} as ct, {user} as u, {course} as c
                WHERE ct.teacher = u.id AND ct.course = c.id AND coursework = ?
                ORDER BY u.lastname, u.firstname ';
        $conditions = array($courseworkId);

        return $DB->get_records_sql($sql, $conditions);
    }

    public static function get_all_course_work_teachers(int $courseworkId)
    {
        $teachers = self::get_only_configured_course_work_teachers($courseworkId);

        if(empty($teachers))
        {
            $teachers = array();
        }

        $studentsWorks = sg::get_students_works($courseworkId);
        foreach($studentsWorks as $work)
        {
            if(self::is_teacher_not_exist_in_array($work->teacher, $teachers))
            {
                $teachers = self::add_teacher_to_array($courseworkId, $work->teacher, $teachers);
            }
        }

        $teachers = self::sort_teachers_array($teachers);

        return $teachers;
    }

    private static function is_teacher_not_exist_in_array(int $teacherId, $teachers) : bool
    {
        foreach($teachers as $teacher)
        {
            if($teacher->teacherid == $teacherId)
            {
                return false;
            }
        }

        return true;
    }

    private static function add_teacher_to_array(int $courseworkId, int $userId, $teachers)
    {
        $teacher = self::get_teacher_from_id($courseworkId, $userId);
        return array_merge($teachers, array($teacher));
    }

    public static function get_teacher_from_id(int $courseworkId, int $userId) 
    {
        global $DB;
        $sql = 'SELECT u.id as teacherid, u.firstname, u.lastname, u.email,  u.phone1, 
                    u.phone2, cs.course as courseid, c.fullname as coursename
                FROM {coursework_students} as cs, {user} as u, {course} as c
                WHERE cs.teacher = u.id AND cs.course = c.id 
                AND coursework = ? AND u.id = ? ';     
        $conditions = array($courseworkId, $userId);

        $teacher = $DB->get_record_sql($sql, $conditions);
        $teacher->quota = 0;

        return $teacher;
    }

    private static function sort_teachers_array($teachers)
    {
        if(count($teachers) > 1)
        {
            usort($teachers, function($a, $b)
            {
                return strcmp(
                    $a->lastname.$a->firstname, 
                    $b->lastname.$b->firstname);
            });
        }

        return $teachers;
    }



}
