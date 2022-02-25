<?php

namespace Coursework\Config\SetSuggestedThemes;

class Overview 
{
    private $course;
    private $cm;

    private $courses;

    function __construct(\stdClass $course, \stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->courses = $this->get_coursework_courses();
    }

    public function get_gui() : string 
    {
        $gui = $this->get_header();
        $gui.= Lib::get_go_to_collections_setup_page($this->cm->id);

        if(count($this->courses))
        {
            $gui.= $this->get_themes_collections_setting();
        }
        else 
        {
            $gui.= $this->set_up_leaders();
        }

        return $gui;
    }

    private function get_coursework_courses()
    {
        $courses = $this->get_coursework_courses_from_database();
        $courses = $this->add_themes_collections_courses($courses);
        $courses = $this->add_themes_list_to_courses($courses);

        return $courses;
    }

    private function get_coursework_courses_from_database()
    {
        global $DB;
        $sql = 'SELECT DISTINCT ct.course AS id, c.fullname AS name
                FROM {coursework_teachers} AS ct 
                INNER JOIN {course} AS c 
                ON ct.course = c.id 
                WHERE ct.coursework = ?';
        $params = array($this->cm->instance);
        return $DB->get_records_sql($sql, $params);
    }

    private function add_themes_collections_courses($courses)
    {
        foreach($courses as $course)
        {
            $course->collection = $this->get_course_themes_collection($course->id);
        }

        return $courses;
    }

    private function get_course_themes_collection(int $courseId)
    {
        global $DB;
        $sql = 'SELECT ctc.id, ctc.name, ctc.description, 
                ccu.samethemescount, ccu.id AS rowid 
                FROM {coursework_collections_use} AS ccu
                INNER JOIN {coursework_themes_collections} AS ctc
                ON ccu.collection = ctc.id
                WHERE ccu.coursework = ?
                AND ctc.course = ?';
        $params = array($this->cm->instance, $courseId);
        return $DB->get_record_sql($sql, $params);
    }

    private function add_themes_list_to_courses($courses)
    {
        foreach($courses as $course)
        {
            if(!empty($course->collection->id))
            {
                $course->themes = $this->get_collection_themes($course->collection->id);
            }
        }

        return $courses;
    }

    private function get_collection_themes(int $collectionId)
    {
        global $DB;
        $where = array('collection' => $collectionId);
        return $DB->get_records('coursework_themes', $where, 'name', 'name');
    }

    private function get_header() : string 
    {
        $text = get_string('set_suggested_themes', 'coursework');
        return \html_writer::tag('h2', $text);
    }

    private function set_up_leaders() : string 
    {
        $text = get_string('set_up_leaders', 'coursework');
        return \html_writer('p', $text);
    }

    private function get_themes_collections_setting() : string 
    {
        $stc = '';

        foreach($this->courses as $course)
        {
            $tsc.= $this->get_theme_collection_setting($course);
        }

        return $tsc;
    }

    private function get_theme_collection_setting(\stdClass $course) : string 
    {
        $str = $this->get_course_header($course->name);

        if(empty($course->collection))
        {
            $str.= $this->get_add_button($course);
            $str.= $this->get_no_suggested_themes();
        }
        else 
        {
            $str.= $this->get_action_buttons($course);
            $str.= $this->get_collection_overview($course);
        }
        
        return $str;
    }

    private function get_course_header(string $courseName) : string 
    {
        $header = get_string('course', 'coursework').': ';

        $attr = array('class' => 'courseName');
        $header.= \html_writer::tag('span', $courseName, $attr);

        return \html_writer::tag('h4', $header);
    }

    private function get_collection_overview(\stdClass $course) : string 
    {
        $view = $this->get_suggested_collection_header();
        $view.= $this->get_collection_name($course->collection);
        $view.= $this->get_same_theme_can_be_selected_times($course->collection);
        $view.= $this->get_collection_description($course->collection);
        $view.= $this->get_collection_themes_list($course);

        return $view;
    }

    private function get_suggested_collection_header() : string
    {
        $text = get_string('suggested_themes_collection', 'coursework');
        $text = \html_writer::tag('b', $text);
        return \html_writer::tag('p', $text);
    }

    private function get_collection_name(\stdClass $collection) : string 
    {
        $text = get_string('name', 'coursework').': ';
        $text = \html_writer::tag('b', $text);
        $text.= $collection->name;
        return \html_writer::tag('p', $text);
    }

    private function get_same_theme_can_be_selected_times(\stdClass $collection)
    {
        $text = get_string('same_theme_can_be_selected_times', 'coursework').': ';
        $text = \html_writer::tag('b', $text);
        $text.= $collection->samethemescount;
        return \html_writer::tag('p', $text);
    }

    private function get_collection_description(\stdClass $collection) : string 
    {
        $text = get_string('description', 'coursework').':';
        $text = \html_writer::tag('b', $text);
        $desc = \html_writer::tag('p', $text);

        if(empty($collection->description))
        {
            $text = get_string('absent', 'coursework');
            $desc.= \html_writer::tag('p', $text);
        }
        else 
        {
            $desc.= \html_writer::tag('p', $collection->description);
        }

        return $desc;
    }

    private function get_collection_themes_list($course) : string 
    {
        $id = 'themes_toggler'.$course->collection->id;

        $attr = array(
            'class' => 'themes_toggler',
            'onclick' => 'toggle_themes_list(`'.$id.'`)'
        );
        $text = get_string('themes_list_toggle', 'coursework');
        $list = \html_writer::tag('p', $text, $attr);
        
        if(count($course->themes))
        {
            $themesList = $this->get_themes_list($course->themes);
        }
        else 
        {
            $text = get_string('absent', 'coursework');
            $themesList = \html_writer::tag('p', $text);
        }

        $attr = array('id' => $id, 'class' => 'themes_list');
        $list.= \html_writer::tag('div', $themesList, $attr);

        return $list;
    }

    private function get_themes_list($themes) : string 
    {
        $list = \html_writer::start_tag('ol');

        foreach($themes as $theme)
        {
            $list.= \html_writer::tag('li', $theme->content);
        }

        $list.= \html_writer::end_tag('ol');

        return $list;
    }

    private function get_action_buttons(\stdClass $course) : string 
    {
        $btns = \html_writer::start_tag('p');
        $btns.= $this->get_change_button($course).' ';
        $btns.= $this->get_delete_button($course->collection);
        $btns.= \html_writer::end_tag('p');

        return $btns;
    }

    private function get_change_button(\stdClass $course) : string
    {
        $attr = array('method' => 'post', 'style' => 'display: inline');
        $btn = \html_writer::start_tag('form', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::ID,
            'value' => $this->cm->id
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::GUI_TYPE,
            'value' => Main::CHANGE_USING_THEMES
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::COLLECTION_ID,
            'value' => $course->collection->id
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::THEMES_USING_ID,
            'value' => $course->collection->rowid
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::COURSE_ID,
            'value' => $course->id
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'submit',
            'value' => get_string('change', 'coursework')
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $btn.= \html_writer::end_tag('form');

        return $btn;
    }

    private function get_delete_button(\stdClass $collection) : string
    {
        $attr = array('method' => 'post', 'style' => 'display: inline');
        $btn = \html_writer::start_tag('form', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::ID,
            'value' => $this->cm->id
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::DATABASE_EVENT,
            'value' => Main::DELETE_THEME_USING
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::GUI_TYPE,
            'value' => Main::OVERVIEW
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::THEMES_USING_ID,
            'value' => $collection->rowid
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

    private function get_no_suggested_themes() : string 
    {
        $text = get_string('no_suggested_themes', 'coursework');
        return \html_writer::tag('p', $text);
    }

    private function get_add_button(\stdClass $course) : string
    {
        $attr = array('method' => 'post');
        $btn = \html_writer::start_tag('form', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::ID,
            'value' => $this->cm->id
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::COURSE_ID,
            'value' => $course->id
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::GUI_TYPE,
            'value' => Main::ADD_THEME_USING
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'submit',
            'value' => get_string('add_suggested_themes', 'coursework')
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $btn.= \html_writer::end_tag('form');

        return $btn;
    }


}
