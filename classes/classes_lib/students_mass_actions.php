<?php 

namespace Coursework\ClassesLib\StudentsMassActions;

require_once '../../lib/getters/common_getter.php';

use Coursework\Lib\Getters\CommonGetter as cg;

class StudentsSelector
{
    private $groups;

    function __construct(array $groups)
    {
        $this->groups = $groups;
    }

    public function get() : string 
    {
        $attr = array('class' => 'studentsSelector');
        $s = \html_writer::start_tag('table', $attr);
        $s.= \html_writer::start_tag('tr');
        $s.= \html_writer::tag('td', get_string('select', 'coursework'));
        $s.= \html_writer::tag('td', $this->get_groups_selector());
        $s.= \html_writer::tag('td', $this->get_cancel_button());
        $s.= \html_writer::end_tag('tr');
        $s.= \html_writer::end_tag('table');

        return $s;
    }

    private function get_groups_selector() : string 
    {
        $attr = array(
            'autocomplete' => 'off',
            'autofocus' => 'autofocus'
        );
        $s = \html_writer::start_tag('select');

        $attr = array(
            'value' => 'all',
            'onclick' => 'select_students_checkboxes(this)'
        );
        $text = get_string('all_participants', 'coursework');
        $s.= \html_writer::tag('option', $text, $attr);

        foreach($this->groups as $group)
        {
            $attr = array(
                'value' => $group->name,
                'onclick' => 'select_students_checkboxes(this)'
            );
            $text = $group->name;
            $s.= \html_writer::tag('option', $text, $attr);
        }

        $s.= \html_writer::end_tag('select');

        return $s;
    }

    private function get_cancel_button() : string 
    {
        $attr = array('onclick' => 'unselect_students_checkboxes()');
        $text = get_string('cancel_choice', 'coursework');
        return \html_writer::tag('button', $text, $attr);
    }

}

class StudentsTable 
{
    const SEPARATOR = '+';
    const STUDENTS = 'students';

    private $formName;
    private $students;

    function __construct(array $students, string $formName)
    {
        $this->students = $students;
        $this->formName = $formName;
    }

    public function get() : string 
    {
        $attr = array('class' => 'studentsMassAction');
        $t = \html_writer::start_tag('table', $attr);
        $t.= $this->table_header();
        $t.= $this->table_body();
        $t.= \html_writer::end_tag('table');

        return $t;
    }

    protected function table_header() : string 
    {
        $h = \html_writer::start_tag('tr');
        $h.= \html_writer::tag('td', '');
        $h.= \html_writer::tag('td', get_string('fullname', 'coursework'));
        $h.= \html_writer::tag('td', get_string('group', 'coursework'));
        $h.= \html_writer::tag('td', get_string('leader', 'coursework'));
        $h.= \html_writer::tag('td', get_string('course', 'coursework'));
        $h.= \html_writer::end_tag('tr');

        return $h;
    }

    protected function table_body() : string 
    {
        $b = '';

        foreach($this->students as $student)
        {
            $attr = $this->get_row_attr($student);
            $b.= \html_writer::start_tag('tr', $attr);
            $b.= $this->get_select_student_checkbox_cell($student);
            $b.= $this->get_student_fullname_cell($student);
            $b.= $this->get_groups_names_cell($student->groups);
            $b.= $this->get_leader_cell($student->teacher);
            $b.= $this->get_course_cell($student->course);
            $b.= \html_writer::end_tag('tr');
        }

        return $b;
    }

    protected function get_row_attr($student)
    {
        return array();
    }

    protected function get_select_student_checkbox_cell(\stdClass $student) : string 
    {
        $value = $student->id.self::SEPARATOR.$student->lastname;
        $value.= ' '.$student->firstname.self::SEPARATOR;

        $attr = array(
            'type' => 'checkbox',
            'form' => $this->formName,
            'class' => 'students '.$this->get_group_classes($student->groups),
            'name' => self::STUDENTS.'[]',
            'value' => $value,
            'autocomplete' => 'off'

        );
        $input = \html_writer::empty_tag('input', $attr);

        return \html_writer::tag('td', $input);
    }

    protected function get_student_fullname_cell(\stdClass $student) : string
    {
        $fullname = $student->lastname.' '.$student->firstname;
        return \html_writer::tag('td', $fullname);
    }

    protected function get_group_classes(array $groups)
    {
        $grClasses = '';

        foreach($groups as $group)
        {
            $grClasses.= $group->name.' ';
        }

        return $grClasses;
    }

    protected function get_groups_names_cell(array $groups) : string 
    {
        if(count($groups) == 1) 
        {
            $text = reset($groups)->name;
        }
        else
        {
            $text = '';

            foreach($groups as $group)
            {
                $text.= $group->name.'<br>';
            }

            return $text;
        }

        return \html_writer::tag('td', $text);
    }

    protected function get_leader_cell(int $leacherId) : string 
    {
        $text = cg::get_user_name($leacherId);
        return \html_writer::tag('td', $text);
    }

    protected function get_course_cell(int $courseId) : string 
    {
        $text = cg::get_course_name($courseId);
        return \html_writer::tag('td', $text);
    }


}

class Lib 
{
    public static function get_distribute_students() : array 
    {
        $students = array();
        $strings = optional_param_array(StudentsTable::STUDENTS, null, PARAM_TEXT);

        foreach($strings as $string) 
        {
            $str = explode(StudentsTable::SEPARATOR, $string);

            $student = new \stdClass;
            $student->id = $str[0];
            $student->fullname = $str[1];

            $students[] = $student;
        }

        return $students;
    }
}
