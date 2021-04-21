<?php

require_once 'not_available_page.php';
require_once 'theme_selection_page.php';

use Coursework\View\StudentsWork\Locallib as locallib;
use Coursework\View\StudentsWork\Components as c;
use Coursework\Lib\Getters\StudentsGetter as sg;
use Coursework\Lib\Getters\CommonGetter as cg;

class ThemeSelectionMain 
{
    private $course;
    private $cm;
    private $studentId;

    function __construct(stdClass $course, stdClass $cm, int $studentId)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->studentId = $studentId;
    }

    public function get_page() : string 
    {
        $page = cg::get_page_header($this->cm);
        $page.= $this->get_guidelines_block();
        $page.= $this->get_theme_selection_block();
        $page.= $this->get_navigation_block();

        return $page;
    }

    private function get_guidelines_block() : string 
    {
        $guidelines = new c\Guidelines($this->course, $this->cm, $this->studentId);
        return $guidelines->get_component();
    }

    private function get_theme_selection_block() : string 
    {
        $header = get_string('view_theme_selection_header', 'coursework');
        $content = $this->get_theme_selection_content();
        
        $themeSelection = new c\Container(
            $this->course, 
            $this->cm, 
            $this->studentId,
            $header,
            $content
        );
        return $themeSelection->get_component();
    }

    private function get_theme_selection_content() : string 
    {
        if($this->is_user_student())
        {
            return $this->get_theme_selection_page();
        }
        else
        {
            return $this->get_not_available_page();
        }
    }

    private function is_user_student() : bool 
    {
        global $USER;

        if($this->studentId == $USER->id)
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    private function get_not_available_page() : string 
    {
        $notAvailable = new ThemeSelectionNotAvailablePage($this->course, $this->cm);
        return $notAvailable->get_page();
    }

    private function get_theme_selection_page() : string 
    {
        $themeSelection = new ThemeSelectionPage($this->course, $this->cm, $this->studentId);
        return $themeSelection->get_page();
    }

    private function get_navigation_block() : string 
    {
        $chat = new c\Navigation($this->course, $this->cm, $this->studentId);
        return $chat->get_component();
    }    


}
