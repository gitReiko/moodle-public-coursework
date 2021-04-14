<?php 

namespace Coursework\Lib;

class CommonLib 
{

    public static function is_user_student(\stdClass $cm, int $userId) : bool 
    {
        $context = \context_module::instance($cm->id);

        if(has_capability('mod/coursework:is_student', $context, $userId))
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    public static function is_user_teacher(\stdClass $cm, int $userId) : bool 
    {
        $context = \context_module::instance($cm->id);

        if(has_capability('mod/coursework:is_teacher', $context, $userId))
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    public static function is_user_manager(\stdClass $cm, int $userId) : bool 
    {
        $context = \context_module::instance($cm->id);

        if(has_capability('mod/coursework:is_manager', $context, $userId))
        {
            return true;
        }
        else 
        {
            return false;
        }
    }





}


