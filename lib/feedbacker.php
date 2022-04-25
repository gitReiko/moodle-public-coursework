<?php 

namespace Coursework\Lib;

use Coursework\Lib\Enums;

class Feedbacker  
{
    const FEEDBACK_ITEM = '⊗';
    const FEEDBACK_STATE = '⊕';

    public static function get_success_feedback(string $text) : \stdClass  
    {
        $feedback = new \stdClass;
        $feedback->text = $text;
        $feedback->success = 1;
        return $feedback;
    }

    public static function get_fail_feedback(string $text) : \stdClass  
    {
        $feedback = new \stdClass;
        $feedback->text = $text;
        $feedback->success = 0;
        return $feedback;
    }

    public static function add_feedback_to_string(string $string, \stdClass $feedback) : string 
    {
        $string.= $feedback->text;
        $string.= self::FEEDBACK_STATE.$feedback->success;
        $string.= self::FEEDBACK_ITEM;
        return $string;
    }

    public static function get_feedback_from_post() : string 
    {
        $text = 0;
        $success = 1;
        $post = optional_param(Enums::FEEDBACK, '', PARAM_TEXT);

        $feedback = '';
        if(!empty($post))
        {
            $postItems = explode(self::FEEDBACK_ITEM, $post);

            foreach($postItems as $postItem)
            {
                $chunks = explode(self::FEEDBACK_STATE, $postItem);

                if($chunks[$success] == '1')
                {
                    $attr = array('class' => 'success-feedback');
                }
                else 
                {
                    $attr = array('class' => 'fail-feedback');
                }

                if(!empty($chunks[$text]))
                {
                    $feedback.= \html_writer::tag('p', $chunks[$text], $attr);
                }
            }
        }

        return $feedback;
    }

}
