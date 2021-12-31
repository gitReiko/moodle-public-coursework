<?php

namespace Coursework\Lib;

abstract class StepByStep 
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

    protected static function get_intro_js_function() : string 
    {
        $func = 'introJs().setOptions({';
        $func.= "nextLabel: '".get_string('next', 'coursework')."', ";
        $func.= "prevLabel: '".get_string('back', 'coursework')."', ";
        $func.= "doneLabel: '".get_string('done', 'coursework')."'})";
        $func.= '.start();';

        return $func;
    }

    protected static function get_explanation(string $text, string $title, string $intro) : string 
    {
        $attr = array(
            'data-title' => $title, 
            'data-intro' => $intro
        );
        return \html_writer::tag('div', $text, $attr);
    }


}

