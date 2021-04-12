<?php 

namespace Coursework\View\StudentsWorksList;

use Coursework\View\StudentsWorksList as swl;

class NotChosenTeacher 
{


    private $studentsCount;

    function __construct(swl\MainGetter $d) 
    {
        $studentsWithoutTeacher = $d->get_students_without_teacher();

        if(is_array($studentsWithoutTeacher))
        {
            $this->studentsCount = count($studentsWithoutTeacher);
        }        
    }

    public function get() : string 
    {
        if($this->studentsCount)
        {
            $text = get_string('students_wihout_teacher_count', 'coursework');
            $text.= ' '.$this->studentsCount;
            
            return \html_writer::tag('p', $text);
        }
        else 
        {
            return '';
        }
    }



}