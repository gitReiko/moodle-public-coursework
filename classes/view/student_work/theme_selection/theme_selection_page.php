<?php

require_once 'data_getters/main.php';

use coursework_lib as lib;

class ThemeSelectionPage
{
    private $course;
    private $cm;
    private $studentId;

    private $leaders;
    private $courses;
    private $themes;

    private $selectedLeader;
    private $selectedCourse;

    function __construct(stdClass $course, stdClass $cm, int $studentId)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->studentId = $studentId;

        $this->init_leaders_and_courses();
    }

    public function get_page() : string 
    {
        $page = $this->get_start_of_html_form();
        $page.= $this->get_leader_field();
        $page.= $this->get_course_field();
        $page.= $this->get_theme_field();
        $page.= $this->get_use_own_theme_field();
        $page.= $this->get_own_theme_field();
        $page.= $this->get_action_buttons();
        $page.= $this->get_neccessary_form_inputs();
        $page.= $this->get_end_of_html_form();
        $page.= $this->get_js_data();
        return $page;
    }

    private function init_leaders_and_courses() : void 
    {
        $getter = new ThemeSelectionMainGetter($this->course, $this->cm);
        $this->leaders = $getter->get_available_leaders();

        // Сourses and leaders can only end at the same time.
        if(empty($this->leaders))
        {
            throw new Exception(get_string('e:quota_is_over', 'coursework'));
        }

        $this->courses = $getter->get_available_courses();
        $this->themes = $getter->get_available_themes();

        // Because selected always first element.
        $this->selectedLeader = reset($this->leaders);
        $this->selectedCourse = reset(reset($this->leaders)->courses);
    }

    private function get_start_of_html_form() : string 
    {
        return '<form name="selectForm" method="post">';
    }

    private function get_leader_field() : string 
    {
        $field = '<h4>'.get_string('leader', 'coursework').'</h4>';
        $field.= $this->get_leaders_select();
        return $field;
    }

    private function get_leaders_select() : string 
    {
        $sel = '<p>';
        $sel.= '<select id="leader_select" ';
        $sel.= ' name="'.TEACHER.'" ';
        $sel.= ' onchange="SelectThemePage.change_available_courses()"';
        $sel.= ' autocomplete="off" autofocus>';
        foreach($this->leaders as $leader)
        {
            $sel.= '<option value="'.$leader->id.'">';
            $sel.= $leader->fullname;
            $sel.= '</option>';
        }
        $sel.= '</select>';
        $sel.= '</p>';
        return $sel;
    }

    private function get_course_field() : string 
    {
        $field = '<h4>'.get_string('course', 'coursework').'</h4>';
        $field.= $this->get_courses_select();
        return $field;
    }

    private function get_courses_select() : string 
    {
        $sel = '<p>';
        $sel.= '<select id="course_select" ';
        $sel.= ' onchange="SelectThemePage.update_themes_select()"';
        $sel.= ' name="'.COURSE.'" ';
        $sel.= ' autocomplete="off">';
        foreach($this->courses as $course)
        {
            if($this->is_course_belong_to_leader($course))
            {
                $sel.= '<option class="course_option" value="'.$course->id.'">';
                $sel.= $course->fullname;
                $sel.= '</option>';
            }
        }
        $sel.= '</select>';
        $sel.= '</p>';
        return $sel;
    }

    private function is_course_belong_to_leader(stdClass $course) : bool 
    {
        foreach($this->selectedLeader->courses as $selCourse)
        {
            if($course->id == $selCourse)
            {
                return true;
            }
        }

        return false;
    }

    private function get_theme_field() : string 
    {
        $field = '<h4>'.get_string('theme', 'coursework').'</h4>';
        $field.= $this->get_theme_select();
        return $field;
    }

    private function get_theme_select() : string 
    {
        $sel = '<p>';
        $sel.= '<select id="theme_select" required ';
        $sel.= ' name="'.THEME.'" ';
        $sel.= ' autocomplete="off" size="10">';
        foreach($this->themes as $container)
        {
            if($container->course == $this->selectedCourse)
            {
                foreach($container->themes as $theme)
                {
                    $sel.= '<option class="course_option" value="'.$theme->id.'">';
                    $sel.= $theme->name;
                    $sel.= '</option>';
                }
            }
        }
        $sel.= '</select>';
        $sel.= '</p>';
        return $sel;
    }

    private function get_use_own_theme_field()
    {
        $field = '<div>';
        $field.= $this->get_use_own_theme_checkbox().' ';
        $field.= '<h4 class="themeSelectCheckbox" onclick="SelectThemePage.use_own_theme()">';
        $field.= get_string('use_own_theme', 'coursework');
        $field.= '</h4>';
        $field.= '</div>';
        return $field;
    }

    private function get_use_own_theme_checkbox()
    {
        $input = '<input type="checkbox" id="useOwnTheme" ';
        $input.= ' onclick="SelectThemePage.offer_or_own_theme_switcher()"';
        $input.= ' autocomplete="off">';
        return $input;
    }

    private function get_own_theme_field() : string 
    {
        $field = '<h4>'.get_string('own_theme', 'coursework').'</h4>';
        $field.= $this->get_own_theme_input();
        return $field;
    }

    private function get_own_theme_input() : string 
    {
        $input = '<p><input type="text" id="own_theme_input"';
        $input.= ' name="'.OWN_THEME.'" ';
        $input.= ' maxlength=254 minlength="5" size="140"';
        $input.= ' disabled autocomplete="off" required></p>';
        return $input;
    }

    private function get_action_buttons() : string 
    {
        $btns = '<table><tr>';
        $btns.= '<td>'.$this->get_select_button().'</td>';
        $btns.= '<td>'.lib\get_back_to_course_button($this->course->id).'</td>';
        $btns.= '</tr></table>';
        return $btns;
    }

    private function get_select_button() : string 
    {
        $btn = '<p><input type="submit" ';
        $btn.= 'value="'.get_string('choose', 'coursework').'" ';
        $btn.= '></p>';
        return $btn;
    }

    private function get_neccessary_form_inputs() : string 
    {
        $inputs = '<input type="hidden" name="'.ID.'" value="'.$this->cm->id.'"/>';
        $inputs.= '<input type="hidden" name="'.DB_EVENT.'" value="'.ViewDatabaseHandler::SELECT_THEME.'">';
        return $inputs;
    }

    private function get_end_of_html_form() : string 
    {
        return '</form>';
    }

    private function get_js_data() : string 
    {
        $data = $this->get_leaders_js_data();
        $data.= $this->get_courses_js_data();
        $data.= $this->get_themes_js_data();
        return $data;
    }

    private function get_leaders_js_data() : string 
    {
        $data = '';
        foreach($this->leaders as $leader)
        {
            $data.= '<p class="hidden leaders_courses_js" ';
            $data.= ' data-leader="'.$leader->id.'" ';

            $data.= ' data-courses="';
            foreach($leader->courses as $course)
            {
                $data.= $course.' ';
            }
            $data = mb_substr($data, 0, -1);
            $data.= '" ></p>';
        }
        return $data;
    }

    private function get_courses_js_data() : string 
    {
        $data = '';
        foreach($this->courses as $course)
        {
            $data.= '<p class="hidden courses_js" ';
            $data.= ' data-id="'.$course->id.'" ';
            $data.= ' data-fullname="'.$course->fullname.'" ';
            $data.= '" ></p>';
        }
        return $data;
    }

    private function get_themes_js_data() : string 
    {
        $data = '';
        foreach($this->themes as $container)
        {
            foreach($container->themes as $theme)
            {   
                $data.= '<p class="hidden themes_js" ';
                $data.= ' data-theme-id="'.$theme->id.'" ';
                $data.= ' data-course-id="'.$container->course.'" ';
                $data.= ' data-name="'.$theme->name.'" ';
                $data.= '" ></p>';
            }
        }
        return $data;  
    }


}
