<?php

abstract class LeadersActionGUI 
{
    protected $course;
    protected $cm;

    protected $courseTeachers;
    protected $siteCourses;

    function __construct($course, $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->courseTeachers = $this->get_all_course_teachers();
        $this->siteCourses = $this->get_all_site_courses();
    }

    public function display() : string 
    {
        $gui = '';
        $gui.= $this->get_html_form_start();
        $gui.= $this->get_action_header();
        $gui.= $this->get_leader_label();
        $gui.= $this->get_leader_select();

        // ------

        $gui.= $this->get_html_form_end();
        return $gui;
    }

    private function get_all_site_courses() : array
    {
        global $DB;
        $courses = array();
        $courses = $DB->get_records('course', array(), 'fullname', 'id, fullname');
        return $courses;
    }

    private function get_all_course_teachers() : array 
    {
        $allRolesOfTeachersArchetypes = cw_get_archetype_roles(array('editingteacher', 'teacher'));
        $allCourseGroups = groups_get_all_groups($this->course->id);
        return cw_get_users_with_archetype_roles_from_group($allCourseGroups, $allRolesOfTeachersArchetypes, $this->course->id, $this->cm->instance);
    }

    private function get_html_form_start() : string { return '<form>'; }

    abstract protected function get_action_header() : string;

    private function get_leader_label() : string 
    {
        return '<h4>'.get_string('leader', 'coursework').'</h4>';
    }

    abstract protected function get_leader_select() : string;

    /*
    private function get_leader_select() : string 
    {

    }

    private function get_course_select() : string 
    {

    }

    private function get_quota() : string 
    {

    }

    abstract protected function get_action_button() : string;

    private function get_back_to_overview_button() : string 
    {

    }

    abstract protected function get_action_form_params() : string;

    */

    private function get_html_form_end() : string { return '</form>'; }
    
} 

