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

    public static function get_appoint_explanation(string $text) : string 
    {
        $title = get_string('leaders_appointment', 'coursework');
        $intro = get_string('appoint_explanation', 'coursework');

        return self::get_explanation($text, $title, $intro);
    }  

    public static function get_leader_explanation(string $text) : string 
    {
        $title = get_string('leader', 'coursework');
        $intro = get_string('leader_explanation', 'coursework');

        return self::get_explanation($text, $title, $intro);
    }

    public static function get_leader_course_explanation(string $text) : string 
    {
        $title = get_string('course', 'coursework');
        $intro = get_string('leader_course_explanation', 'coursework');

        return self::get_explanation($text, $title, $intro);
    }

    public static function get_quota_explanation(string $text) : string 
    {
        $title = get_string('quota', 'coursework');
        $intro = get_string('quota_explanation', 'coursework');

        return self::get_explanation($text, $title, $intro);
    }

    public static function get_edit_button_explanation(string $text) : string 
    {
        $title = get_string('editing', 'coursework');
        $intro = get_string('no_effect_on_choice_made', 'coursework');

        return self::get_explanation($text, $title, $intro);
    }

    public static function get_delete_button_explanation(string $text) : string 
    {
        $title = get_string('deleting', 'coursework');
        $intro = get_string('no_effect_on_choice_made', 'coursework');

        return self::get_explanation($text, $title, $intro);
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

    private static function get_explanation(string $text, string $title, string $intro) : string 
    {
        $attr = array(
            'data-title' => $title, 
            'data-intro' => $intro, 
            'class' => 'card__body'
        );
        return \html_writer::tag('div', $text, $attr);
    }


}

