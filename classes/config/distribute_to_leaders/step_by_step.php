<?php

namespace Coursework\Config\DistributeToLeaders;

use Coursework\Lib as lib;

class StepByStep extends lib\StepByStep
{

    public static function get_students_distribution_explanation(string $text) : string 
    {
        $title = get_string('students_distribution', 'coursework');
        $intro = get_string('students_distribution_explanation', 'coursework');

        return parent::get_explanation($text, $title, $intro);
    }

    public static function get_students_selection_explanation(string $text) : string 
    {
        $title = get_string('students_distribution', 'coursework');
        $intro = get_string('distribute_students_selection', 'coursework');

        return parent::get_explanation($text, $title, $intro);
    }

    public static function get_leader_explanation(string $text) : string 
    {
        $title = get_string('leader', 'coursework');
        $intro = get_string('only_appointed_leaders', 'coursework');

        return parent::get_explanation($text, $title, $intro);
    }

    public static function get_course_explanation(string $text) : string 
    {
        $title = get_string('course', 'coursework');
        $intro = get_string('only_appointed_courses', 'coursework');

        return parent::get_explanation($text, $title, $intro);
    }

    public static function get_quota_increase_explanation(string $text) : string 
    {
        $title = get_string('quota_increase', 'coursework');
        $intro = get_string('quota_increase_explanation', 'coursework');

        return parent::get_explanation($text, $title, $intro);
    }



}
