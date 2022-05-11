<?php

namespace Coursework\Lib\Getters;

class UserGetter
{

    public static function get_user(int $userId) : \stdClass
    {
        global $DB;
        $where = array('id' => $userId);
        $user = $DB->get_record('user', $where);

        if($user)
        {
            return $user;
        }
        else 
        {
            $deleted = new \stdClass;
            $deleted->id = $userId;
            $deleted->firstname = '';
            $deleted->lastname = get_string('deleted_user_num', 'coursework', $userId);
            $deleted->email = 'deleted.user'.$userId.'@qwe.nomail';
            $deleted->phone1 = '';
            $deleted->phone2 = '';

            return $deleted;
        }
    }

    public static function get_user_fullname(int $userId) : string 
    {
        global $DB;
        $where = array('id' => $userId);
        $user = $DB->get_record('user', $where, 'firstname,lastname');

        if($user)
        {
            return $user->lastname.' '.$user->firstname;
        }
        else 
        {
            return get_string('deleted_user_num', 'coursework', $userId);
        }
    }

    public static function get_user_photo($userID) : string
    {
        global $DB, $OUTPUT;

        $user = $DB->get_record('user', array('id' => $userID));

        if($user)
        {
            return $OUTPUT->user_picture($user);
        }
        else 
        {
            return '';
        }
    }

    public static function get_chat_user_photo($userID) : string
    {
        global $DB, $OUTPUT;

        $user = $DB->get_record('user', array('id' => $userID));

        if($user)
        {
            return $OUTPUT->user_picture($user, array('size' => 50));
        }
        else 
        {
            return '';
        }
    }

    public static function get_big_user_photo($userID) : string
    {
        global $DB, $OUTPUT;

        $user = $DB->get_record('user', array('id' => $userID));

        if($user)
        {
            return $OUTPUT->user_picture($user, array('size' => 100));
        }
        else 
        {
            return '';
        }
    }

}
