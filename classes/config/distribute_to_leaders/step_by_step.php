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



}
