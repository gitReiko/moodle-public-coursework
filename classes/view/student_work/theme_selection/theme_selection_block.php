<?php

namespace Coursework\View\StudentWork\ThemeSelection;

use Coursework\View\DatabaseHandlers\Main as MainDB;
use Coursework\View\DatabaseHandlers\Main as db;
use Coursework\Lib\CommonLib as cl; 

require_once 'data_getters/main.php';
require_once 'data_getters/neccessary_javascript.php';

class ThemeSelectionBlock
{
    private $course;
    private $cm;
    private $studentId;

    private $availableTeachers;
    private $availableCourses;
    private $availableThemes;
    private $selectedCourses;
    private $selectedThemes;
    private $selectedTeacher;
    private $selectedCourse;

    function __construct(\stdClass $course, \stdClass $cm, int $studentId)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->studentId = $studentId;

        $getter = new MainGetter($this->course, $this->cm, $this->studentId);

        $this->availableTeachers = $getter->get_available_teachers();

        if(empty($this->availableTeachers))
        {
            throw new \Exception(get_string('e:quota_is_over', 'coursework'));
        }

        $this->availableCourses = $getter->get_available_courses();
        $this->availableThemes = $getter->get_available_themes();

        $this->selectedCourses = $getter->get_selected_courses();
        $this->selectedThemes = $getter->get_selected_themes();

        $this->selectedTeacher = $getter->get_selected_teacher();
        $this->selectedCourse = $getter->get_selected_course();
    }

    public function get_block() : string 
    {
        global $USER;

        if(cl::is_user_student($this->cm, $USER->id))
        {
            return $this->get_select_theme_block();
        }
        else 
        {
            return $this->get_theme_no_selected();
        }
    }

    private function get_select_theme_block() : string 
    {
        $page = $this->get_start_of_html_form();
        $page.= $this->get_teacher_select();
        $page.= $this->get_course_select();
        $page.= $this->get_theme_select();
        $page.= $this->get_use_own_theme_checkbox();
        $page.= $this->get_own_theme_input();
        $page.= $this->get_select_theme_button();
        $page.= $this->get_neccessary_form_inputs();
        $page.= $this->get_end_of_html_form();
        $page.= $this->get_js_data();

        return $page;
    }

    private function get_theme_no_selected() : string 
    {
        $text = get_string('theme_no_selected', 'coursework');
        return \html_writer::tag('p', $text);
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
        $name = MainDB::TEACHER;
        $onchange = 'SelectThemePage.change_available_courses()';
        $options = $this->get_teachers_options();

        return $this->get_select($title, $id, $name, $onchange, $options);
    }

    private function get_select(
        string $title, 
        string $id, 
        string $name, 
        string $onchange, 
        string $options, 
        int $size = 1, 
        bool $disabled = false
    ) : string 
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

        if($disabled)
        {
            $attr = array_merge($attr, array('disabled' => 'disabled'));
        }

        $select = \html_writer::tag('select', $options, $attr);
        $select = \html_writer::tag('p', $select);

        return $header.$select;
    }

    private function get_teachers_options() : string 
    {
        $options = '';
        foreach($this->availableTeachers as $teacher)
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
        $name = MainDB::COURSE;
        $onchange = 'SelectThemePage.update_themes_select()';
        $options = $this->get_courses_options();

        return $this->get_select($title, $id, $name, $onchange, $options);
    }

    private function get_courses_options()
    {
        $options = '';
        foreach($this->selectedCourses as $course)
        {
            $attr = array('class' => 'course_option', 'value' => $course->id);
            $text = $course->name;
            $options.= \html_writer::tag('option', $text, $attr);
        }

        return $options;
    }

    private function is_proposed_themes_exists() : bool 
    {
        foreach($this->availableThemes as $container)
        {
            if($container->course == $this->selectedCourse->id)
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
        $name = MainDB::THEME;
        $onchange = '';
        $options = $this->get_themes_options();
        $size = 10;

        if($this->is_proposed_themes_exists())
        {
            $disabled = false;
        }
        else 
        {
            $disabled = true;
        }

        return $this->get_select($title, $id, $name, $onchange, $options, $size, $disabled);
    }

    private function get_themes_options() : string 
    {
        if($this->is_proposed_themes_exists())
        {
            return $this->get_proposed_themes_options();
        }
        else 
        {
            return $this->get_missing_themes_option();
        }
    }

    private function get_proposed_themes_options() : string 
    {
        $options = '';
        foreach($this->selectedThemes as $theme)
        {
            $attr = array('class' => 'course_option', 'value' => $theme->id);
            $text = $theme->content;
            $options.= \html_writer::tag('option', $text, $attr);
        }

        return $options;
    }

    private function get_missing_themes_option() : string 
    {
        $attr = array('class' => 'course_option', 'value' => 0);
        $text = get_string('themes_missing', 'coursework');
        return \html_writer::tag('option', $text, $attr);
    }

    private function get_use_own_theme_checkbox()
    {
        $attr = array(
            'type' => 'checkbox',
            'id' => 'useOwnTheme',
            'autocomplete' => 'off'
        );

        if($this->is_proposed_themes_exists())
        {
            $onclick = 'SelectThemePage.use_own_theme();';
            $onclick.= 'SelectThemePage.offer_or_own_theme_switcher();';
            $attr = array_merge($attr, array('onclick' => $onclick));
        }
        else
        {
            $attr = array_merge($attr, array('disabled' => 'disabled'));
        }

        $input = \html_writer::empty_tag('input', $attr);

        $attr = array(
            'id' => 'useOwnThemeParagraph',
            'class' => 'pageHeader themeSelectCheckbox'
        );

        if($this->is_proposed_themes_exists())
        {
            $attr = array_merge($attr, array('onclick' => 'SelectThemePage.use_own_theme()'));
        }

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
            'name' => MainDB::OWN_THEME,
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
            'name' => MainDB::ID,
            'value' => $this->cm->id
        );
        $inputs = \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => MainDB::DB_EVENT,
            'value' => db::SELECT_THEME
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
            $this->availableTeachers,
            $this->availableCourses,
            $this->availableThemes
        );
        return $js->get();
    }


}
