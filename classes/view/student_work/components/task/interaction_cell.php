<?php

namespace Coursework\View\StudentsWork\Components\Task;

class InteractionCell 
{

    function __construct()
    {

    }

    public function get() : string 
    {
        return \html_writer::tag('td', '');
    }


}
