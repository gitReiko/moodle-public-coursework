<?php

namespace Coursework\View\StudentsWorksList\StudentsNamesFilter;

use Coursework\View\StudentsWorksList as swl;

require_once 'name_filter.php';
require_once 'firstname_filter.php';
require_once 'lastname_filter.php';

class Main 
{
    const ALL = 'all';
    const LASTNAME = 'lastname';
    const FIRSTNAME = 'firstname';

    private $d;

    function __construct(swl\MainGetter $d) 
    {
        $this->d = $d;
    }

    public function get_students_names_filter() : string 
    {
        $filter = $this->get_lastname_filter();
        $filter.= $this->get_firstname_filter();

        return $filter;
    }

    private function get_lastname_filter()
    {
        $lastname = new LastnameFilter($this->d);
        return $lastname->get_name_filter();
    }

    private function get_firstname_filter()
    {
        $firstname = new FirstnameFilter($this->d);
        return $firstname->get_name_filter();
    }

}
