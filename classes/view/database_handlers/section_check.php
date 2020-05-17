<?php

use coursework_lib as lib;

class SectionsCheckDatabaseHandler 
{
    private $course;
    private $cm;
    private $sectionStatus;

    function __construct(stdClass $course, stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->sectionStatus = $this->get_section_status();
    }

    public function handle()
    {
        $this->update_section_status();
    }

    private function get_section_status() : stdClass 
    {
        $sectionStatus = new stdClass;
        $sectionStatus->coursework = $this->get_coursework();
        $sectionStatus->student = $this->get_student();
        $sectionStatus->section = $this->get_section();
        $sectionStatus->status = $this->get_status();
        $sectionStatus->timemodified = time();
        return $sectionStatus;
    }

    private function get_coursework() : int 
    {
        if(empty($this->cm->instance)) throw new Exception('Missing coursework id.');
        return $this->cm->instance;
    }

    private function get_student() : int 
    {
        $student = optional_param(STUDENT, null, PARAM_INT);
        if(empty($student)) throw new Exception('Missing student id.');
        return $student;
    }

    private function get_section() : int 
    {
        $section= optional_param(SECTION, null, PARAM_INT);
        if(empty($section)) throw new Exception('Missing section id.');
        return $section;
    }

    private function get_status() : string  
    {
        $status= optional_param(STATUS, null, PARAM_TEXT);
        if(empty($status)) throw new Exception('Missing status.');
        return $status;
    }

    private function get_section_status_id() : int  
    {
        global $DB;
        $where = array('coursework'=>$this->cm->instance, 
                        'student' => $this->sectionStatus->student,
                        'section' => $this->sectionStatus->section);
        return $DB->get_field('coursework_sections_status', 'id', $where);
    }

    private function update_section_status()
    {
        global $DB;
        $this->sectionStatus->id = $this->get_section_status_id();
        return $DB->update_record('coursework_sections_status', $this->sectionStatus);
    }





}
