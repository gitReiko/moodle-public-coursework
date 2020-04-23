<?php

class ThemeSelectionNotAvailablePage 
{

    public function get_page() : string 
    {
        // + navigation
        return '<p>'.get_string('interaction_with_student_work_will_be_available_after_theme_selection', 'coursework').'</p>';
    }


}
