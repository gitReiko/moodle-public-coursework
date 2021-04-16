<?php

namespace Coursework\View\StudentsWork;

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


}
