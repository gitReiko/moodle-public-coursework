<?php 

namespace Coursework\Lib;

require_once 'getters/common_getter.php';

use Coursework\Lib\Getters\CommonGetter as cg;

class CommonLib 
{

    public static function is_user_student(\stdClass $cm, int $userId) : bool 
    {
        $context = \context_module::instance($cm->id);

        if(
            (has_capability('mod/coursework:is_student', $context, $userId))
            && !(has_capability('mod/coursework:is_teacher', $context, $userId))
            && !(has_capability('mod/coursework:is_manager', $context, $userId))
        )
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

        if((has_capability('mod/coursework:is_teacher', $context, $userId))
            && !(has_capability('mod/coursework:is_manager', $context, $userId))
        )
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

    public static function is_coursework_use_task(int $courseworkId) : bool 
    {
        $coursework = cg::get_coursework($courseworkId);

        if($coursework->usetask == 1)
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    public static function is_theme_collection_used(int $courseworkId, int $courseId) : bool 
    {
        global $DB;
        $sql = 'SELECT ctc.id 
                FROM {coursework_themes_collections_use} AS ctcu
                INNER JOIN {coursework_themes_collections} AS ctc
                ON ctcu.collection = ctc.id
                WHERE ctcu.coursework = ?
                AND ctc.course = ?';
        $params = array($courseworkId, $courseId);
        return $DB->record_exists_sql($sql, $params);
    }


}


