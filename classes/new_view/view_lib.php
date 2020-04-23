<?php 

namespace view_lib;

function get_back_to_works_list_button(\stdClass $cm) : string 
{
    $btn = '<td>';
    $btn.= '<form>';
    $btn.= '<input type="hidden" name="'.ID.'" value="'.$cm->id.'" >';
    $btn.= '<button>';
    $btn.= get_string('back_to_works_list', 'coursework');
    $btn.= '</button>';
    $btn.= '</form>';
    $btn.= '</td>';
    return $btn;
}



