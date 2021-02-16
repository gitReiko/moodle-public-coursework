<?php

namespace view\quota_overview;

require_once 'getter.php';

class Main 
{
    private $cm;
    private $d;

    function __construct(\stdClass $cm) 
    {
        $this->cm = $cm;
        $this->d = new Getter($cm);

        print_r($this->d);
    }

    public function get_page() : string  
    {
        $p = '';
        $p.= $this->get_page_header();

        return $p;
    }

    private function get_page_header() : string  
    {
        $text = get_string('quota_overview', 'coursework');
        return \html_writer::tag('h3', $text);
    }

}

