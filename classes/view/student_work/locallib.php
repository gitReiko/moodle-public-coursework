<?php

namespace Coursework\View\StudentWork;

use Coursework\Lib\Getters\StudentsGetter as sg;
use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\CommonLib as cl;
use Coursework\Lib\Enums as enum;

class Locallib 
{

    public static function is_user_teacher(\stdClass $work) : bool 
    {
        global $USER;

        if($USER->id == $work->teacher)
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    public static function is_user_student(\stdClass $work) : bool 
    {
        global $USER;

        if($USER->id == $work->student)
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    public static function is_user_student_or_teacher(\stdClass $work) : bool 
    {
        if(self::is_user_student($work))
        {
            return true;
        }
        else if(self::is_user_teacher($work))
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    public static function is_state_not_ready_or_returned_for_rework(string $status) : bool 
    {
        if($status == enum::NOT_READY)
        {
            return true;
        }
        else if($status == enum::RETURNED_FOR_REWORK)
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    public static function is_state_sent_for_check(string $status) : bool 
    {
        if($status == enum::SENT_FOR_CHECK)
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    public static function is_state_ready(string $status) : bool 
    {
        if($status == enum::READY)
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    public static function get_students_list_for_in_query(\stdClass $cm) : string 
    {
        $inQuery = '';
        $students = sg::get_all_students($cm);
        foreach($students as $student)
        {
            $inQuery.= $student->id.',';
        }
        $inQuery = mb_substr($inQuery, 0, -1);
        return $inQuery;
    }

    public static function get_count_of_same_themes(int $cmInstance, int $courseId) 
    {
        if(cl::is_theme_collection_used($cmInstance, $courseId))
        {
            $usedCollection = cg::get_used_theme_collection($cmInstance, $courseId);
            return $usedCollection->countofsamethemes;
        }
        else 
        {
            return 0;
        }
    }

    public static function get_count_of_theme_usages(int $cmInstance, int $themeId, string $students) : int 
    {
        global $DB;
        $sql = "SELECT COUNT(id)
                FROM {coursework_students}
                WHERE coursework = ?
                AND theme = ?
                AND student IN ($students)";
        $where = array($cmInstance, $themeId);
        $count = $DB->count_records_sql($sql, $where);

        if(empty($count)) return 0;
        else return $count;
    }

    public static function is_theme_not_used(int $usagesCount, int $availableCountOfUsages) : bool
    {
        if(intval($usagesCount) < intval($availableCountOfUsages))
        {
            return true;
        }
        else 
        {
            return false;
        }
    }


}
