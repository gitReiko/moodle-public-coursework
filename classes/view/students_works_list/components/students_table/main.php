<?php 

namespace Coursework\View\StudentsWorksList\StudentsTable;

require_once 'thead.php';
require_once 'tbody.php';

use Coursework\View\StudentsWorksList as swl;

class Main 
{
    const MORE = 'more_';
    const MORE_POINTER = 'more_pointer_';

    private $d;

    function __construct(swl\MainGetter $d) 
    {
        $this->d = $d;
    }

    public static function get_more_details_class(int $studentId) : string
    {
        return self::MORE.$studentId;
    }

    public static function get_more_details_btn_id(int $studentId) : string 
    {
        return self::MORE_POINTER.$studentId;
    }

    public static function get_indent_from_blank_cells() : string 
    {
        $attr = array('class' => 'no-borders');
        $indent = \html_writer::tag('td', '', $attr);
        $indent.= \html_writer::tag('td', '', $attr);
        return $indent;
    }

    public function get_students_table() : string 
    {
        if($this->is_teacher_students_exist())
        {
            return $this->get_table();
        }
        else 
        {
            return $this->no_students_message();
        }
    }

    private function is_teacher_students_exist() : bool 
    {
        $students = $this->d->get_students();

        if(is_array($students))
        {
            if(count($students))
            {
                return true;
            }
            else 
            {
                return false;
            }
        }
        else 
        {
            return false;
        }
    }

    private function get_table() : string 
    {
        $attr = array('class' => 'studentsWorksList');
        $tbl = \html_writer::start_tag('table', $attr);
        $tbl.= $this->get_table_head();
        $tbl.= $this->get_table_body();
        $tbl.= \html_writer::end_tag('table');

        return $tbl;
    }

    private function get_table_head() : string 
    {
        $thead = new Thead();
        return $thead->get();
    }

    private function get_table_body() : string 
    {
        $tbody = new Tbody($this->d);
        return $tbody->get();
    }

    private function no_students_message() : string 
    {
        $attr = array('class' => 'no_students_message');
        $text = get_string('no_students_matching_filters', 'coursework');
        return \html_writer::tag('p', $text, $attr);
    }


}
