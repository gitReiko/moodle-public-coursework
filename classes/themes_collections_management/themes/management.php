<?php

namespace Coursework\View\ThemesCollectionsManagement\Themes;

use Coursework\View\ThemesCollectionsManagement\Main;
use Coursework\Lib\Getters\CoursesGetter as coug;
use Coursework\Lib\Getters\CommonGetter as cg;

class Management
{
    private $course;
    private $cm;

    private $collection;
    private $themes;

    function __construct($course, $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->collection = $this->get_collection();
        $this->themes = $this->get_collection_themes();
    }

    public function get_gui() : string
    {
        $gui = $this->get_themes_management_header();
        $gui.= $this->get_course_header();
        $gui.= $this->get_themes_list();
        $gui.= $this->get_back_to_overview_button();

        $this->log_themes_viewed();

        return $gui;
    }

    private function get_collection() : \stdClass 
    {
        $collectionId = $this->get_collection_id();
        global $DB;
        return $DB->get_record('coursework_themes_collections', array('id'=>$collectionId));
    }

    private function get_collection_id() 
    {
        $id = optional_param(Main::COLLECTION_ID, null, PARAM_INT);
        if(empty($id)) throw new \Exception('Missing collection id.');
        return $id;
    }

    private function get_collection_themes()
    {
        global $DB;
        return $DB->get_records('coursework_themes', array('collection'=>$this->collection->id), 'content');
    }

    private function get_themes_management_header() : string 
    {
        $attr = array('style' => 'color:grey;');
        $text = get_string('collection_themes_management_header', 'coursework').' ';
        $text = \html_writer::tag('span', $text, $attr);
        $text.= $this->collection->name;

        return \html_writer::tag('h3', $text);
    }

    private function get_course_header() : string 
    {
        $attr = array('style' => 'color:grey;');
        $text = get_string('collection_course_header', 'coursework').' ';
        $text = \html_writer::tag('span', $text, $attr);
        $text.= coug::get_course_fullname($this->collection->course);

        return \html_writer::tag('h3', $text);
    }

    private function get_themes_list() : string
    {
        $str = \html_writer::start_tag('ol');

        foreach ($this->themes as $theme)
        {
            $text = $theme->content;
            $text.= $this->get_edit_button($theme);
            $text.= $this->get_delete_button($theme);

            $str.= \html_writer::tag('li', $text);
        }

        $str.= $this->get_add_new_button();
        $str.= \html_writer::end_tag('ol');

        return $str;
    }

    private function get_edit_button(\stdClass $theme) : string
    {
        $attr = array(
            'style' => 'display:inline;',
            'method' => 'post',
            'action' => Main::MODULE_URL
        );
        $btn = ' '.\html_writer::start_tag('form', $attr);

        $btn.= $this->get_common_themes_inputs();

        $attr = array(
            'type' => 'hidden',
            'name' => Main::DATABASE_EVENT,
            'value' => Main::EDIT_THEME
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::THEME_ID,
            'value' => $theme->id
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'id' => 'theme'.$theme->id,
            'type' => 'text',
            'name' => Main::CONTENT,
            'minlength' => 5,
            'maxlength' => 255,
            'required' => 'required',
            'size' => 80,
            'autocomplete' => 'off',
            'style' => 'display:none;'
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'submit',
            'value' => get_string('edit', 'coursework'),
            'onclick' => $this->get_edit_theme_js_func($theme)
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $btn.= \html_writer::end_tag('form');

        return $btn;
    }

    private function get_edit_theme_js_func(\stdClass $theme) : string 
    {
        $enterText = get_string('enter_new_theme_name', 'coursework');
        $errorText = get_string('error_theme_not_changed', 'coursework');

        $func = 'edit_theme('.$theme->id.',`'.$theme->content.'`,';
        $func.= '`'.$enterText.'`, `'.$errorText.'`)';

        return $func;
    }

    private function get_common_themes_inputs() : string 
    {
        $attr = array(
            'type' => 'hidden',
            'name' => Main::ID,
            'value' => $this->cm->id
        );
        $btn = \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::GUI_TYPE,
            'value' => Main::THEMES_MANAGEMENT
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::COLLECTION_ID,
            'value' => $this->collection->id
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        return $btn;
    }

    private function get_delete_button(\stdClass $theme) : string
    {
        $attr = array(
            'style' => 'display:inline;',
            'method' => 'post',
            'action' => Main::MODULE_URL
        );
        $btn = ' '.\html_writer::start_tag('form', $attr);

        $btn.= $this->get_common_themes_inputs();

        $attr = array(
            'type' => 'hidden',
            'name' => Main::DATABASE_EVENT,
            'value' => Main::DELETE_THEME
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::THEME_ID,
            'value' => $theme->id
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'submit',
            'value' => get_string('delete', 'coursework')
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $btn.= \html_writer::end_tag('form');

        return $btn;
    }

    private function get_add_new_button() : string
    {
        $btn = \html_writer::start_tag('li');

        $attr = array(
            'method' => 'post',
            'action' => Main::MODULE_URL
        );
        $btn.= \html_writer::start_tag('form', $attr);

        $btn.= $this->get_common_themes_inputs();

        $attr = array(
            'type' => 'hidden',
            'name' => Main::DATABASE_EVENT,
            'value' => Main::ADD_THEME
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'text',
            'name' => Main::CONTENT,
            'minlength' => 5,
            'maxlength' => 255,
            'required' => 'required',
            'autofocus' => 'autofocus',
            'size' => 80,
            'autocomplete' => 'off'
        );
        $btn.= \html_writer::empty_tag('input', $attr).' ';

        $attr = array(
            'type' => 'submit',
            'value' => get_string('add_new_theme', 'coursework')
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $btn.= \html_writer::end_tag('form');

        $btn.= \html_writer::end_tag('li');

        return $btn;
    }

    private function get_back_to_overview_button() : string 
    {
        $btn = \html_writer::start_tag('p');

        $attr = array(
            'method' => 'post',
            'action' => Main::MODULE_URL
        );
        $btn.= \html_writer::start_tag('form', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::ID,
            'value' => $this->cm->id
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::GUI_TYPE,
            'value' => Main::OVERVIEW
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'submit',
            'value' => get_string('back', 'coursework')
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $btn.= \html_writer::end_tag('form');

        $btn.= \html_writer::end_tag('p');

        return $btn;
    }

    private function log_themes_viewed() : void 
    {
        $params = array
        (
            'context' => \context_module::instance($this->cm->id)
        );
        
        $event = \mod_coursework\event\themes_viewed::create($params);
        $event->trigger();
    }

}


