<?php

namespace Coursework\Lib\Getters;

use Coursework\Lib\Enums as enum;

class CommonGetter
{

    public static function get_page_header(\stdClass $cm) : string 
    {
        $attr = array('class' => 'pageHeader');
        $text = self::get_coursework_name($cm->instance);
        return \html_writer::tag('h2', $text, $attr);
    }

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

    public static function get_default_coursework_task(\stdClass $cm)
    {
        global $DB;
        $sql = 'SELECT ct.*, ctu.id as rowid
                FROM {coursework_tasks} AS ct
                INNER JOIN {coursework_default_task_use} AS ctu
                ON ct.id = ctu.task
                WHERE coursework = ?';
        $conditions = array($cm->instance);
        return $DB->get_record_sql($sql, $conditions);
    }

    public static function get_task_sections(int $taskId)
    {
        global $DB;
        $conditions = array('task' => $taskId);
        return $DB->get_records('coursework_tasks_sections', $conditions, 'listposition, name');
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

    public static function get_chat_user_photo($userID) : string
    {
        global $DB, $OUTPUT;

        $user = $DB->get_record('user', array('id' => $userID));
        $photo = $OUTPUT->user_picture($user, array('size' => 50));

        return $photo;
    }

    public static function get_big_user_photo($userID) : string
    {
        global $DB, $OUTPUT;

        $user = $DB->get_record('user', array('id' => $userID));
        $photo = $OUTPUT->user_picture($user, array('size' => 100));

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

    public static function get_user(int $userId) : \stdClass
    {
        global $DB;
        $where = array('id' => $userId);
        return $DB->get_record('user', $where);
    }

    public static function get_user_name(int $id) : string
    {
        global $DB;
        $user = $DB->get_record('user', array('id'=>$id), 'id, firstname, lastname');
        return $user->lastname.' '.$user->firstname;
    }

    public static function get_course_name(int $id) : string 
    {
        global $DB;
        $where = array('id' => $id);
        return $DB->get_field('course', 'fullname', $where);
    }

    public static function get_course_fullname($courseId) : string
    {
        global $DB;
        $where = array('id' => $courseId);
        return $DB->get_field('course', 'fullname', $where);
    }


}
