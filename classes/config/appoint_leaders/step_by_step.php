<?php

namespace Coursework\Config\AppointLeaders;

class StepByStep 
{

    public static function get_help_button() : string 
    {
        $attr = array(
            'type' => 'button',
            'value' => get_string('help', 'coursework'),
            'onclick' => 'introJs().start();'
        );
        $input = \html_writer::empty_tag('input', $attr);
        return \html_writer::tag('p', $input);
    }

    public static function get_leader_explanation(string $text) : string 
    {
        $attr = array(
            'data-title' => get_string('leader', 'coursework'),
            'data-intro' => get_string('leader_explanation', 'coursework'),
            'class' => 'card-demo'
        );
        return \html_writer::tag('div', $text, $attr);
    }


}

