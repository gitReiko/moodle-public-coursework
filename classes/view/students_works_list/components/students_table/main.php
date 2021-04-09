<?php 

namespace Coursework\View\StudentsWorksList\StudentsTable;

require_once 'thead.php';

use Coursework\View\StudentsWorksList as swl;

class Main 
{

    private $d;

    function __construct(swl\MainGetter $d) 
    {
        $this->d = $d;
    }

    public function get_students_table() : string 
    {
        $attr = array('class' => 'studentsWorksList');
        $tbl = \html_writer::start_tag('table', $attr);
        $tbl.= $this->get_table_head();
        //$tbl.= $this->get_table_body();
        $tbl.= \html_writer::end_tag('table');

        return $tbl;
    }

    private function get_table_head() : string 
    {
        $thead = new Thead();
        return $thead->get();
    }


}
