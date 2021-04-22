<?php

require_once 'data_getters/main.php';
require_once 'data_getters/neccessary_javascript.php';

class ThemeSelectionBlock
{
    private $course;
    private $cm;
    private $studentId;

    private $teachers;
    private $courses;
    private $themes;

    private $selectedTeacher;
    private $selectedCourse;

    function __construct(stdClass $course, stdClass $cm, int $studentId)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->studentId = $studentId;

        $getter = new ThemeSelectionMainGetter($this->course, $this->cm);

        $this->teachers = $getter->get_available_teachers();

        if(empty($this->teachers))
        {
            throw new Exception(get_string('e:quota_is_over', 'coursework'));
        }

        $this->courses = $getter->get_available_courses();
        $this->themes = $getter->get_available_themes();

        $this->selectedTeacher = $getter->get_selected_teacher();
        $this->selectedCourse = $getter->get_selected_course();
    }

    public function get_block() : string 
    {
        $page = $this->get_start_of_html_form();
        $page.= $this->get_teacher_select();
        $page.= $this->get_course_select();

        if($this->is_proposed_themes_exists())
        {
            $page.= $this->get_theme_select();
            $page.= $this->get_use_own_theme_checkbox();
        }
        else 
        {
            $page.= $this->get_missing_proposed_themes();
        }
        
        $page.= $this->get_own_theme_input();
        $page.= $this->get_select_theme_button();
        $page.= $this->get_neccessary_form_inputs();
        $page.= $this->get_end_of_html_form();
        $page.= $this->get_js_data();
        return $page;
    }

    private function get_start_of_html_form() : string 
    {
        $attr = array(
            'name' => 'selectForm',
            'method' => 'post'
        );
        return \html_writer::start_tag('form', $attr);
    }

    private function get_teacher_select() : string 
    {
        $title = get_string('leader', 'coursework');
        $id = 'leader_select';
        $name = TEACHER;
        $onchange = 'SelectThemePage.change_available_courses()';
        $options = $this->get_teachers_options();

        return $this->get_select($title, $id, $name, $onchange, $options);
    }

    private function get_select(string $title, string $id, string $name, string $onchange, string $options, int $size = 1) : string 
    {
        $attr = array('class' => 'pageHeader');
        $header = \html_writer::tag('p', $title, $attr);
        
        $attr = array(
            'id' => $id,
            'name' => $name,
            'onchange' => $onchange,
            'size' => $size,
            'autocomplete' => 'off',
            'autofocus' => 'autofocus'
        );
        $select = \html_writer::tag('select', $options, $attr);
        $select = \html_writer::tag('p', $select);

        return $header.$select;
    }

    private function get_teachers_options() : string 
    {
        $options = '';
        foreach($this->teachers as $teacher)
        {
            $attr = array('value' => $teacher->id);
            $text = $teacher->name;
            $options.= \html_writer::tag('option', $text, $attr);
        }

        return $options;
    }

    private function get_course_select() : string 
    {
        $title = get_string('course', 'coursework');
        $id = 'course_select';
        $name = COURSE;
        $onchange = 'SelectThemePage.update_themes_select()';
        $options = $this->get_courses_options();

        return $this->get_select($title, $id, $name, $onchange, $options);
    }

    private function get_courses_options()
    {
        $options = '';
        foreach($this->courses as $course)
        {
            $attr = array('class' => 'course_option', 'value' => $course->id);
            $text = $course->name;
            $options.= \html_writer::tag('option', $text, $attr);
        }

        return $options;
    }

    private function is_proposed_themes_exists() : bool 
    {
        foreach($this->themes as $container)
        {
            if($container->course == $this->selectedCourse)
            {
                if(is_array($container->themes))
                {
                    if(count($container->themes))
                    {
                        return true;
                    }
                    else 
                    {
                        return false;
                    }
                }
                else 
                {
                    return fales;
                }
            }
        }

        return false;
    }

    private function get_theme_select() : string 
    {
        $title = get_string('proposed_themes', 'coursework');
        $id = 'theme_select';
        $name = THEME;
        $onchange = '';
        $options = $this->get_themes_options();
        $size = 10;

        return $this->get_select($title, $id, $name, $onchange, $options, $size);
    }

    private function get_themes_options() : string 
    {
        $options = '';
        foreach($this->themes as $container)
        {
            if($container->course == $this->selectedCourse)
            {
                foreach($container->themes as $theme)
                {
                    $attr = array('class' => 'course_option', 'value' => $theme->id);
                    $text = $theme->name;
                    $options.= \html_writer::tag('option', $text, $attr);
                }
            }
        }

        return $options;
    }

    private function get_missing_proposed_themes() : string 
    {
        $attr = array('class' => 'pageHeader');
        $text = get_string('proposed_themes', 'coursework');
        $str = \html_writer::tag('p', $text, $attr);

        $text = get_string('themes_missing', 'coursework');
        $str.= \html_writer::tag('p', $text);
        
        return $str;
    }

    private function get_use_own_theme_checkbox()
    {
        $onclick = 'SelectThemePage.use_own_theme();';
        $onclick.= 'SelectThemePage.offer_or_own_theme_switcher();';

        $attr = array(
            'type' => 'checkbox',
            'id' => 'useOwnTheme',
            'onclick' => $onclick,
            'autocomplete' => 'off'
        );
        $input = \html_writer::empty_tag('input', $attr);

        $attr = array(
            'class' => 'pageHeader themeSelectCheckbox',
            'onclick' => 'SelectThemePage.use_own_theme()'
        );
        $text = $input;
        $text.= ' '.get_string('use_own_theme', 'coursework');

        return \html_writer::tag('p', $text, $attr);
    }

    private function get_own_theme_input() : string 
    {
        $attr = array('class' => 'pageHeader');
        $text = get_string('own_theme', 'coursework');
        $header = \html_writer::tag('p', $text, $attr);

        $attr = array(
            'type' => 'text',
            'id' => 'own_theme_input',
            'name' => OWN_THEME,
            'minlength' => 5,
            'maxlength' => 254,
            'size' => 140,
            'autocomplete' => 'off',
            'required' => 'required'
        );

        if($this->is_proposed_themes_exists())
        {
            $attr = array_merge($attr, array('disabled' => 'disabled'));
        }

        $input = \html_writer::empty_tag('input', $attr);
        $input = \html_writer::tag('p', $input);

        return $header.$input;
    }

    private function get_select_theme_button() : string 
    {
        $text = get_string('choose', 'coursework');
        $button = \html_writer::tag('button', $text);
        
        return \html_writer::tag('p', $button);
    }

    private function get_neccessary_form_inputs() : string 
    {
        $attr = array(
            'type' => 'hidden',
            'name' => ID,
            'value' => $this->cm->id
        );
        $inputs = \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => DB_EVENT,
            'value' => ViewDatabaseHandler::SELECT_THEME
        );
        $inputs.= \html_writer::empty_tag('input', $attr);

        return $inputs;
    }

    private function get_end_of_html_form() : string 
    {
        return \html_writer::end_tag('form');
    }

    private function get_js_data() : string 
    {
        $js = new NeccessaryJavascript(
            $this->teachers,
            $this->courses,
            $this->themes
        );
        return $js->get();
    }


}
