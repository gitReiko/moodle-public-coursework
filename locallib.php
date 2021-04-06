<?php

namespace CourseWork;

class LocalLib
{

    const NO_GROUPS = 0;
    const ISOLATED_GROUPS = 1;
    const VISIBLE_GROUPS = 2;

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

    public static function get_all_students(\stdClass $cm)
    {
        $context = \context_module::instance($cm->id); 
        $groupId = self::NO_GROUPS;
        $userfields = 'u.id,u.firstname,u.lastname,u.email,u.phone1,u.phone2';
        $orderby = 'u.lastname';

        return get_enrolled_users(
            $context, 
            'mod/coursework:is_student',
            $groupId, 
            $userfields, 
            $orderby
        );
    }

    public static function get_students_from_available_groups(\stdClass $cm, $groups)
    {
        $availableGroups = array();

        foreach($groups as $group)
        {
            $availableGroups = array_merge(
                $availableGroups,
                self::get_students_from_group($cm, $group->id)
            );
        }

        usort($availableGroups, function($a, $b)
        {
            return strcmp(
                $a->lastname.$a->firstname, 
                $b->lastname.$b->firstname);
        });

        return $availableGroups;
    }

    public static function get_students_from_group(\stdClass $cm, int $groupId)
    {
        $context = \context_module::instance($cm->id); 
        $userfields = 'u.id,u.firstname,u.lastname,u.email,u.phone1,u.phone2';
        $orderby = 'u.lastname';

        return get_enrolled_users(
            $context, 
            'mod/coursework:is_student',
            $groupId, 
            $userfields, 
            $orderby
        );
    }

    public static function get_teachers(int $courseworkId)
    {
        $teachers = self::get_configurated_teachers($courseworkId);
    
        return $teachers;
    }

    /**
     * Returns teachers from coursework_teachers database table.
     */
    private static function get_configurated_teachers(int $courseworkId)
    {
        global $DB;
        $sql = 'SELECT ct.id as recordid, ct.teacher as teacherid, u.firstname, u.lastname, 
                    ct.course as courseid, c.fullname as coursename, ct.quota
                FROM {coursework_teachers} as ct, {user} as u, {course} as c
                WHERE ct.teacher = u.id AND ct.course = c.id AND coursework = ?';
        $conditions = array($courseworkId);
    
        $teachers = $DB->get_records_sql($sql, $conditions);
        $teachers = cw_add_fullnames_to_users_array($teachers);
    
        usort($teachers, function($a, $b)
        {
            return strcmp($a->fullname, $b->fullname);
        });
    
        return $teachers;
    }




}





