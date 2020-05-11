<?php

use coursework_lib as lib;

class WorkCheck extends ViewModule 
{
    private $taskSections;

    function __construct(stdClass $course, stdClass $cm, int $studentId, bool $displayBlock = false)
    {
        parent::__construct($course, $cm, $studentId, $displayBlock);

        $this->taskSections = $this->get_need_to_check_task_sections();

        print_r($this->taskSections);
    }

    protected function get_module_name() : string
    {
        return 'workcheck';
    }

    protected function get_module_header() : string
    {
        return get_string('work_check', 'coursework');
    }

    protected function get_module_body() : string
    {
        $body = $this->get_need_to_check_buttons();
        return $body;
    }

    private function get_need_to_check_task_sections()
    {
        global $DB;
        $sql = 'SELECT cts.*, css.timemodified AS tasksubmissiondate 
                FROM {coursework_tasks_sections} AS cts 
                INNER JOIN {coursework_sections_status} AS css
                ON cts.id = css.section 
                WHERE css.coursework = ?
                AND css.student = ? 
                AND css.status = ? 
                ORDER BY listposition';
        $params = array($this->cm->instance, $this->studentId, SENT_TO_CHECK);
        return $DB->get_records_sql($sql, $params);
    }

    private function get_need_to_check_buttons() : string 
    {
        $btns = '';
        foreach($this->taskSections as $section)
        {
            $btns.= $this->get_section_check_block($section);
        }
        return $btns;
    }

    private function get_section_check_block(stdClass $section) : string 
    {
        $block = '';
        $block.= "<p title='{$section->description}'><b>".$section->name.'</b></p>';
        $block.= '<button>';




        return $block;
    }









}

