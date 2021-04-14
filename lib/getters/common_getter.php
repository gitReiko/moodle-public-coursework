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

    public static function get_user_photo($userID) : string
    {
        global $DB, $OUTPUT;

        $user = $DB->get_record('user', array('id' => $userID));
        $photo = $OUTPUT->user_picture($user);

        return $photo;
    }

    public static function get_state_name($status) 
    {
        switch($status)
        {
            case enum::NOT_READY:
                return get_string('work_not_ready', 'coursework');
            case enum::READY:
                return get_string('work_ready', 'coursework');
            case enum::NEED_TO_FIX:
                return get_string('work_need_to_fix', 'coursework');
            case enum::SENT_TO_CHECK:
                return get_string('work_sent_to_check', 'coursework');
        }
    }

    public static function get_user_name(int $id) : string
    {
        global $DB;
        $user = $DB->get_record('user', array('id'=>$id), 'id, firstname, lastname');
        return $user->lastname.' '.$user->firstname;
    }


}
