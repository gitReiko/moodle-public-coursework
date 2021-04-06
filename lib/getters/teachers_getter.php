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

    public static function get_all_course_work_teachers(int $courseworkId, $students)
    {
        $teachers = self::get_only_configured_course_work_teachers($courseworkId);

        $studentsWorks = sg::get_students_works(courseworkId);

    }

    //private static function 

    /*
    public static function add_not_configurated_teachers_from_students_array(
        int $courseworkId, array $teachers, array $students
    ) : array
    {
        foreach($students as $student)
        {
            if(self::is_student_assigned_to_teacher($courseworkId, $student->id))
            {
                if(self::is_teacher_missing_in_teachers_array($teachers, $student))
                {
                    $teachers = self::add_teacher_to_teachers_array(
                        $teachers, $student->teacherid, $student->courseid
                    );
                }
            }
        }

        return $teachers;
    }

    private static function is_student_assigned_to_teacher(int $courseworkId, int $studentId) : bool 
    {
        global $DB;
        $conditions = array 
        (
            'coursework' => $courseworkId,
            'student' => $studentId,
        );
        return $DB->record_exists('coursework_students', $conditions);
    }

    private static function is_teacher_missing_in_teachers_array(array $teachers, \stdClass $student) : bool 
    {
        foreach($teachers as $teacher)
        {
            if(($teacher->teacherid == $student->teacherid)
                && ($teacher->courseid == $student->courseid))
            {
                return false;
            }
        }

        return true;
    }

    private static function add_teacher_to_teachers_array(array $teachers, int $studentTeacher, int $courseId) : array
    {
        $teacher = self::get_teacher($studentTeacher, $courseId);
        $teachers = array_merge($teachers, array($teacher));

        usort($teachers, function($a, $b)
        {
            return strcmp(
                $a->lastname.$a->firstname, 
                $b->lastname.$b->firstname
            );
        });

        return $teachers;
    }

    private static function get_teacher(int $teacherId, int $courseId) : \stdClass
    {
        $teacher = self::get_user($teacherId);
        $teacher->teacherid = $teacherId;
        $teacher->courseid = $courseId;
        $teacher->coursename = self::get_course_fullname($courseId);
        $teacher->quota = 0;

        return $teacher;
    }

    public static function get_user(int $id) : \stdClass
    {
        global $DB;
        $where = array('id'=>$id);
        $select = 'id,firstname,lastname,email,phone1,phone2';
        return $DB->get_record('user', $where, $select);
    }

    public static function get_course_fullname(int $courseid) : string 
    {
        global $DB;
        $where = array('id'=>$courseid);
        return $DB->get_field('course', 'fullname', $where);
    }
    */


}
