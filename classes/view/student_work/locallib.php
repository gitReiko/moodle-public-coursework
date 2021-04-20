<?php

namespace Coursework\View\StudentsWork;

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

    public static function is_state_not_ready_or_need_to_fix(string $status) : bool 
    {
        if($status == enum::NOT_READY)
        {
            return true;
        }
        else if($status == enum::NEED_TO_FIX)
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
        if($status == enum::SENT_TO_CHECK)
        {
            return true;
        }
        else 
        {
            return false;
        }
    }


}
