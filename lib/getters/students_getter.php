<?php

namespace Coursework\Lib\Getters;

use Coursework\Lib\Enums as enum;

class StudentsGetter
{

    public static function get_all_students(\stdClass $cm)
    {
        $context = \context_module::instance($cm->id); 
        $groupId = enum::NO_GROUPS;
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

    public static function get_students_works(int $courseworkId) 
    {
        global $DB;
        $where = array('coursework' => $courseworkId);
        return $DB->get_records('coursework_students', $where);
    }

}

