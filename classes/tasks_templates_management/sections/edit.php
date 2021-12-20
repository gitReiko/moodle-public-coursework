<?php

namespace Coursework\View\TasksTemplatesManagement\Sections;

use Coursework\View\TasksTemplatesManagement\Main;

class Edit extends Action 
{
    private $section;

    function __construct($course, $cm)
    {
        parent::__construct($course, $cm);

        $this->section = $this->get_section();
    }

    protected function get_action_header() : string
    {
        return '<h3>'.get_string('edit_task_section_header', 'coursework').'</h3>';
    }

    protected function get_name_input_value() : string
    {
        return $this->section->name;
    }

    protected function get_description_text() : string
    {
        return $this->section->description;
    }

    protected function get_list_position_input_value() : string
    {
        return $this->section->listposition;
    }

    protected function get_completion_date_value() : string
    {
        if(empty($this->section->completiondate))
        {
            return '';
        }
        else 
        {
            return date('Y-m-d', $this->section->completiondate);
        }
        
    }

    protected function get_action_button() : string
    {
        return '<p><input type="submit" value="'.get_string('save_changes', 'coursework').'" ></p>';
    }

    protected function get_unique_form_hidden_inputs() : string
    {
        $inputs = '<input type="hidden" name="'.Main::DATABASE_EVENT.'" value="'.Main::EDIT_SECTION.'"/>';
        $inputs.= '<input type="hidden" name="'.Main::SECTION_ID.'" value="'.$this->section->id.'">';
        return $inputs;
    }

    private function get_section() : \stdClass
    {
        $sectionId = optional_param(Main::SECTION_ID, null, PARAM_INT);

        if(empty($sectionId)) throw new \Exception('Missing task section id.');

        global $DB;
        return $DB->get_record('coursework_tasks_sections', array('id'=>$sectionId));
    }

}


