<?php

namespace Coursework\Support\BackToWorkState;

class LocalLib
{

    public static function get_student_id($notNull = true)  
    {
        $studentId = optional_param(Main::STUDENT_ID, null, PARAM_INT);

        if(is_null($studentId) && $notNull) 
        {
            throw new \Exception('Missing student id.');
        }

        return $studentId;
    }

}
