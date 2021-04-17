<?php 

namespace Coursework\View\ManageOldFilesArea;

use Coursework\Lib\Getters\CommonGetter as cg;

class Page 
{

    private $cm;

    function __construct(\stdClass $cm)
    {
        $this->cm = $cm;
    }

    public function get_page() : string 
    {
        $page = cg::get_page_header($this->cm);

        return $page;
    }



}
