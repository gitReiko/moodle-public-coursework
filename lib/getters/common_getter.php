<?php

namespace Coursework\Lib\Getters;

use Coursework\Lib\Enums as enum;

class CommonGetter
{

    public static function get_coursework(int $courseworkId) : \stdClass 
    {
        global $DB;
        return $DB->get_record('coursework', array('id'=> $courseworkId));
    }

    public static function get_coursework_name(int $courseworkId) : string
    {
        global $DB;
        return $DB->get_field('coursework', 'name', array('id'=> $courseworkId));
    }

    public static function get_coursework_group_mode(\stdClass $cm) 
    {
        return groups_get_activity_groupmode($cm);
    }

    public static function get_coursework_groups(\stdClass $cm) 
    {
        return groups_get_activity_allowed_groups($cm);
    }

    public static function get_coursework_theme_name(int $themeId)
    {
        global $DB;
        $where = array('id'=> $themeId);
        return $DB->get_field('coursework_themes', 'name', $where);
    }


}
