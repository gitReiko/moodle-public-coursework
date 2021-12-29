<?php

namespace Coursework\Config\AppointLeaders;

class StepByStep 
{

    public static function get_help_button() : string 
    {
        $attr = array(
            'type' => 'button',
            'value' => get_string('help', 'coursework'),
            'onclick' => self::get_intro_js_function()
        );
        $input = \html_writer::empty_tag('input', $attr);
        return \html_writer::tag('p', $input);
    }

    private static function get_intro_js_function() : string 
    {
        $func = 'introJs().setOptions({';
        $func.= "nextLabel: '".get_string('next', 'coursework')."', ";
        $func.= "prevLabel: '".get_string('back', 'coursework')."', ";
        $func.= "doneLabel: '".get_string('done', 'coursework')."'})";
        $func.= '.start();';

        return $func;
    }

    public static function get_leader_explanation(string $text) : string 
    {
        $attr = array(
            'data-title' => get_string('leader', 'coursework'),
            'data-intro' => get_string('leader_explanation', 'coursework'),
            'class' => 'card__body'
        );
        return \html_writer::tag('div', $text, $attr);
    }

    public static function get_leader_course_explanation(string $text) : string 
    {
        $attr = array(
            'data-title' => get_string('course', 'coursework'),
            'data-intro' => get_string('leader_course_explanation', 'coursework'),
            'class' => 'card__body'
        );
        return \html_writer::tag('div', $text, $attr);
    }

    public static function get_quota_explanation(string $text) : string 
    {
        $attr = array(
            'data-title' => get_string('quota', 'coursework'),
            'data-intro' => get_string('quota_explanation', 'coursework'),
            'class' => 'card__body'
        );
        return \html_writer::tag('div', $text, $attr);
    }


}

