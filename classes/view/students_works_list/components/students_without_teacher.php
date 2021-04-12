<?php 

namespace Coursework\View\StudentsWorksList;

use Coursework\View\StudentsWorksList as swl;

class StudentsWithoutTeacher 
{
    const HIDDEN_BLOCK = 'hidden_block_students_without_teacher';

    private $students;
    private $studentsCount;

    function __construct(swl\MainGetter $d) 
    {
        $studentsWithoutTeacher = $d->get_students_without_teacher();

        if(is_array($studentsWithoutTeacher))
        {
            $this->students = $studentsWithoutTeacher;
            $this->studentsCount = count($studentsWithoutTeacher);
        }        
    }

    public function get() : string 
    {
        $str = '';

        if($this->studentsCount)
        {
            $str = $this->get_students_count_btn();
            $str.= $this->get_students_list_block();
        }

        return $str;
    }

    private function get_students_count_btn() : string 
    {
        $attr = array(
            'class' => 'cursorPointer', 
            'onclick' => 'open_close_div(`'.self::HIDDEN_BLOCK.'`)'
        );

        $text = get_string('students_wihout_teacher_count', 'coursework');
        $text.= ' '.$this->studentsCount;
        $text.= ' '.get_string('click_to_open_close_list', 'coursework');
        
        return \html_writer::tag('p', $text, $attr);
    }

    private function get_students_list_block() : string 
    {
        $attr = array('id' => self::HIDDEN_BLOCK, 'class' => 'hidden');
        $str = \html_writer::start_tag('div', $attr);
        $str.= \html_writer::start_tag('ol');

        foreach($this->students as $student) 
        {
            $text = $student->lastname.' '.$student->firstname;
            $str.= \html_writer::tag('li', $text);
        }

        $str.= \html_writer::end_tag('ol');
        $str.= \html_writer::end_tag('div');

        return $str;
    }



}