<?php 

namespace Coursework\Classes\Lib\StudentsMassActions;

require_once '../../lib/getters/courses_getter.php';
require_once '../../lib/getters/user_getter.php';
require_once '../../lib/step_by_step.php';

use Coursework\Lib\Getters\CoursesGetter as coug;
use Coursework\Lib\Getters\UserGetter as ug;
use Coursework\Lib as stepLib;

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
        $s.= $this->get_groups_selector_cell();
        $s.= $this->get_cancel_button_cell();
        $s.= \html_writer::end_tag('tr');
        $s.= \html_writer::end_tag('table');

        return $s;
    }

    private function get_groups_selector_cell() : string 
    {
        $attr = array('title' => get_string('mass_selection_explanation', 'coursework'));
        $text = $this->get_groups_selector();
        $text = StepByStep::get_select_all_explanation($text);
        
        return \html_writer::tag('td', $text, $attr);
    }

    private function get_cancel_button_cell() : string 
    {
        $attr = array('title' => get_string('mass_unselection_explanation', 'coursework'));
        $text = $this->get_cancel_button();
        $text = StepByStep::get_unselect_all_explanation($text);
        
        return \html_writer::tag('td', $text, $attr);
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

    private function table_header() : string 
    {
        $h = \html_writer::start_tag('tr');
        $h.= \html_writer::tag('td', '');
        $h.= \html_writer::tag('td', get_string('fullname', 'coursework'));
        $h.= \html_writer::tag('td', get_string('group', 'coursework'));
        $h.= $this->get_custom_header_cells();
        $h.= \html_writer::end_tag('tr');

        return $h;
    }

    protected function get_custom_header_cells() : string 
    {
        $h.= \html_writer::tag('td', get_string('leader', 'coursework'));
        $h.= \html_writer::tag('td', get_string('course', 'coursework'));

        return $h;
    }

    private function table_body() : string 
    {
        $b = '';
        $i = 0;

        foreach($this->students as $student)
        {
            $attr = $this->get_row_attr($student);
            $b.= \html_writer::start_tag('tr', $attr);
            $b.= $this->get_select_student_checkbox_cell($student, $i);
            $b.= $this->get_student_fullname_cell($student);
            $b.= $this->get_groups_names_cell($student->groups);
            $b.= $this->get_custom_row_cells($student);
            $b.= \html_writer::end_tag('tr');
            $i++;
        }

        return $b;
    }

    protected function get_custom_row_cells(\stdClass $student) : string 
    {
        $cells.= $this->get_leader_cell($student->teacher);
        $cells.= $this->get_course_cell($student->course);

        return $cells;
    }

    protected function get_row_attr($student)
    {
        return array();
    }

    protected function get_select_student_checkbox_cell(\stdClass $student, int $i) : string 
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

        if(empty($i))
        {
            $input = StepByStep::get_select_explanation($input);
        }

        return \html_writer::tag('td', $input);
    }

    private function get_student_fullname_cell(\stdClass $student) : string
    {
        $fullname = $student->lastname.' '.$student->firstname;
        return \html_writer::tag('td', $fullname);
    }

    protected function get_group_classes($groups)
    {
        $grClasses = '';

        foreach($groups as $group)
        {
            $grClasses.= $group->name.' ';
        }

        return $grClasses;
    }

    private function get_groups_names_cell($groups) : string 
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
        }

        return \html_writer::tag('td', $text);
    }

    private function get_leader_cell($leacherId) : string 
    {
        if(empty($leacherId))
        {
            $text = '';
        }
        else 
        {
            $text = ug::get_user_fullname($leacherId);
        }
        
        return \html_writer::tag('td', $text);
    }

    private function get_course_cell($courseId) : string 
    {
        if(empty($courseId))
        {
            $text = '';
        }
        else 
        {
            $text = coug::get_course_fullname($courseId);
        }

        return \html_writer::tag('td', $text);
    }


}

class StepByStep extends stepLib\StepByStep
{

    public static function get_select_all_explanation(string $text) : string 
    {
        $title = get_string('mass_selection', 'coursework');
        $intro = get_string('mass_selection_explanation', 'coursework');

        return parent::get_explanation($text, $title, $intro);
    }

    public static function get_unselect_all_explanation(string $text) : string 
    {
        $title = get_string('mass_selection', 'coursework');
        $intro = get_string('mass_unselection_explanation', 'coursework');

        return parent::get_explanation($text, $title, $intro);
    }

    public static function get_select_explanation(string $text) : string 
    {
        $title = '';
        $intro = get_string('select_required_students', 'coursework');

        return parent::get_explanation($text, $title, $intro);
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

    public static function get_action_students_inputs($students, $formName = null) : string 
    {
        $inputs = '';
        foreach($students as $student)
        {
            $attr = array(
                'type' => 'hidden',
                'name' => StudentsTable::STUDENTS.'[]',
                'value' => $student->id.StudentsTable::SEPARATOR.$student->fullname
            );

            if($formName)
            {
                $attr = array_merge($attr, array('form' => $formName));
            }

            $inputs.= \html_writer::empty_tag('input', $attr);
        }

        return $inputs;
    }

}
