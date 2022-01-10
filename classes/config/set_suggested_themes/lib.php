<?php

namespace Coursework\Config\SetSuggestedThemes;

class Lib 
{

    public static function get_go_to_collections_setup_page(int $courseModuleId) : string 
    {
        $url = '/mod/coursework/pages/themes_collections_management.php?id='.$courseModuleId;
        $attr = array('href' => $url);
        $text = get_string('go_to_collections_setup_page', 'coursework');
        $text = \html_writer::tag('a', $text, $attr);
        return \html_writer::tag('p', $text);
    }


}
