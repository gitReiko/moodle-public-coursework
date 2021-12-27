<?php

namespace Coursework\View\StudentsWorksList\StudentsNamesFilter;

use Coursework\View\StudentsWorksList as swl;

class LastnameFilter extends NameFilter
{

    function __construct(swl\MainGetter $d) 
    {
        parent::__construct($d);
    }

    protected function get_all_letters()
    {
        $letters = array();

        foreach($this->d->get_students() as $student)
        {
            $letters[] = mb_substr($student->lastname, 0, 1);
        }

        return $letters;
    }

    protected function get_header_text() : string
    {
        return get_string('lastname', 'coursework');
    }

    protected function get_input_name() : string
    {
        return Main::LASTNAME;
    }

    protected function is_letter_selected($letter) : bool
    {
        if($this->d->get_lastname_filter() == $letter)
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

}
