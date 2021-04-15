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


}
