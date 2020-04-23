<?php

use view_lib as view;

class ThemeSelectionNotAvailablePage 
{
    private $course;
    private $cm;

    function __construct(stdClass $course, stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;
    }

    public function get_page() : string 
    {
        $page = $this->get_not_available_message();
        $page.= view\get_back_to_works_list_button($this->cm);
        return $page;
    }

    private function get_not_available_message() : string 
    {
        return '<p>'.get_string('interaction_with_student_work_will_be_available_after_theme_selection', 'coursework').'</p>';
    }


}
