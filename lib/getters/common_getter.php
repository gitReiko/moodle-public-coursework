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
        $sql = 'SELECT ct.* 
                FROM {coursework_tasks} AS ct
                INNER JOIN {coursework} AS c
                ON ct.id = c.defaulttask
                WHERE c.id = ?';
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
        return $DB->get_field('coursework_themes', 'content', $where);
    }

    public static function get_state_name($status) 
    {
        switch($status)
        {
            case enum::STARTED:
                return get_string('work_started', 'coursework');
            case enum::READY:
                return get_string('work_ready', 'coursework');
            case enum::RETURNED_FOR_REWORK:
                return get_string('work_returned_for_rework', 'coursework');
            case enum::SENT_FOR_CHECK:
                return get_string('work_sent_for_check', 'coursework');
            case enum::THEME_RESELECTION:
                return get_string('work_theme_reselection', 'coursework');
            case enum::TASK_RECEIPT:
                return get_string('work_task_receipt', 'coursework');
        }
    }

    public static function get_used_theme_collection(int $courseworkId, int $courseId) : \stdClass 
    {
        global $DB;
        $sql = 'SELECT ctc.id, ctc.name, ctc.description, 
                ctcu.samethemescount, ctcu.id AS rowid 
                FROM {coursework_themes_collections_use} AS ctcu
                INNER JOIN {coursework_themes_collections} AS ctc
                ON ctcu.collection = ctc.id
                WHERE ctcu.coursework = ?
                AND ctc.course = ?';
        $params = array($courseworkId, $courseId);
        return $DB->get_record_sql($sql, $params);
    }


}
