<?php

namespace Coursework\Config\AppointLeaders;

use Coursework\Lib as lib;

class StepByStep extends lib\StepByStep
{

    public static function get_appoint_explanation(string $text) : string 
    {
        $title = get_string('leaders_appointment', 'coursework');
        $intro = get_string('appoint_explanation', 'coursework');

        return parent::get_explanation($text, $title, $intro);
    }

    public static function get_appoint_adding_explanation(string $text) : string 
    {
        $title = get_string('adding', 'coursework');
        $intro = get_string('adding_leader_appoint', 'coursework');

        return parent::get_explanation($text, $title, $intro);
    } 

    public static function get_appoint_editing_explanation(string $text) : string 
    {
        $title = get_string('editing', 'coursework');
        $intro = get_string('editing_leader_appoint', 'coursework').'<br><br>';;
        $intro.= get_string('no_effect_on_choice_made', 'coursework');

        return parent::get_explanation($text, $title, $intro);
    } 

    public static function get_leader_explanation(string $text) : string 
    {
        $title = get_string('leader', 'coursework');
        $intro = get_string('leader_explanation', 'coursework');

        return parent::get_explanation($text, $title, $intro);
    }

    public static function get_leaders_list_explanation(string $text) : string 
    {
        $title = get_string('leader', 'coursework');
        $intro = get_string('leader_explanation', 'coursework').'<br><br>';
        $intro.= get_string('only_teachers_in_list', 'coursework');

        return parent::get_explanation($text, $title, $intro);
    }

    public static function get_leader_course_explanation(string $text) : string 
    {
        $title = get_string('course', 'coursework');
        $intro = get_string('leader_course_explanation', 'coursework');

        return parent::get_explanation($text, $title, $intro);
    }

    public static function get_courses_list_explanation(string $text) : string 
    {
        $title = get_string('course', 'coursework');
        $intro = get_string('leader_course_explanation', 'coursework').'<br><br>';
        $intro.= get_string('in_list_all_site_courses', 'coursework');

        return parent::get_explanation($text, $title, $intro);
    }

    public static function get_quota_explanation(string $text) : string 
    {
        $title = get_string('quota', 'coursework');
        $intro = get_string('quota_explanation', 'coursework');

        return parent::get_explanation($text, $title, $intro);
    }

    public static function get_edit_button_explanation(string $text) : string 
    {
        $title = get_string('editing', 'coursework');
        $intro = get_string('no_effect_on_choice_made', 'coursework');

        return parent::get_explanation($text, $title, $intro);
    }

    public static function get_delete_button_explanation(string $text) : string 
    {
        $title = get_string('deleting', 'coursework');
        $intro = get_string('no_effect_on_choice_made', 'coursework');

        return parent::get_explanation($text, $title, $intro);
    }

}
