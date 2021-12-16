<?php 

namespace Coursework\View\ManageOldFilesArea;

use Coursework\Lib\Enums as enum;

class Dashboard 
{
    const FORM_ID = 'manage_old_files_dashboard';

    private $cm;
    private $teachers;
    private $selectedTeacherId;

    function __construct(\stdClass $cm, $teachers, $selectedTeacherId)
    {
        $this->cm = $cm;
        $this->teachers = $teachers;
        $this->selectedTeacherId = $selectedTeacherId;
    }

    public function get() : string 
    {
        $dash = $this->get_form_start();
        $dash.= $this->get_neccessary_form_params();
        $dash.= $this->get_teacher_selector();
        $dash.= $this->get_form_end();

        return $dash;
    }

    private function get_form_start() : string 
    {
        $attr = array(
            'id' => self::FORM_ID,
            'method' => 'post'
        );
        return \html_writer::start_tag('form', $attr);
    }

    private function get_neccessary_form_params() : string 
    {
        $attr = array(
            'type' => 'hidden',
            'name' => enum::ID,
            'value' => $this->cm->id
        );
        return \html_writer::empty_tag('input', $attr);
    }

    private function get_teacher_selector() : string 
    {
        $attr = array(
            'name' => Main::SELECTED_TEACHER_ID,
            'onchange' => 'submit_form(`'.self::FORM_ID.'`)',
            'autocomplete' => 'off'
        );
        $sel = \html_writer::start_tag('select', $attr);

        foreach($this->teachers as $teacher)
        {
            $attr = array('value' => $teacher->id);

            if($this->selectedTeacherId == $teacher->id)
            {
                $attr = array_merge($attr, array('selected' => 'selected'));
            }

            $sel.= \html_writer::start_tag('option', $attr);
            $sel.= $teacher->lastname.' '.$teacher->firstname;
            $sel.= \html_writer::end_tag('option');
        }

        $sel.= \html_writer::end_tag('select');

        return $sel;
    }

    private function get_form_end() : string 
    {
        return \html_writer::end_tag('form');
    }

}
