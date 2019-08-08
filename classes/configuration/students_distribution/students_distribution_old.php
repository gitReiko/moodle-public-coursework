<?php

use coursework_lib as cw;

class StudentsDistribution 
{
    const DISTRIBUTION_TYPE = 'distr_type';
    const DISTRIBUTE_ALL_STUDENTS = 'distr_all'; // all groups
    const DISTRIBUTE_GROUP = 'distr_group';
    const DISTRIBUTE_STUDENTS = 'distr_students';

    private $course;
    private $cm;

    private $groupmode; // 1 - separate groups; 2 - visible groups; null - no groups

    private $cwLeaders;

    function __construct($course, $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->groupmode = groups_get_activity_groupmode($cm);

        $this->cwLeaders = $this->get_coursework_leaders();

        print_r($this->cwLeaders);
    }

    public function execute() : string 
    {



        return $this->get_gui();
    }

    private function get_coursework_leaders() : array 
    {
        global $DB;
        $conditions = array('coursework'=>$this->cm->instance);
        $leaders = $DB->get_records('coursework_teachers', $conditions);

        if(empty($leaders)) 
        {
            throw new Exception(get_string('e-sd:no_leaders', 'coursework')); 
        }

        $this->add_course_names_to_leaders($leaders);
        $this->add_leaders_names_to_leaders($leaders);
        
        return $leaders;
    }

    private function add_course_names_to_leaders(array &$leaders) : void 
    {
        foreach($leaders as $leader)
        {
            $leader->coursename = cw\get_course_name($leader->course);
        }
    }

    private function add_leaders_names_to_leaders(array &$leaders) : void 
    {
        foreach($leaders as $leader)
        {
            $leader->leadername = cw\get_user_name($leader->teacher);
        }
    }

    private function get_gui() : string 
    {
        $gui = '<table class="students_distribution">';
        $gui.= $this->get_table();


        $gui.= '</table>';
        return $gui;
    }

    private function get_table() : string 
    {
        $table = $this->get_table_header();
        $table.= $this->get_group_distribution_header();
        $table.= $this->get_all_participants_row();

        return $table;
    }

    private function get_table_header() : string 
    {
        $header = '<tr>';
        $header.= '<td></td>';
        $header.= '<td>'.get_string('leader', 'coursework').'</td>';
        $header.= '<td>'.get_string('course', 'coursework').'</td>';
        $header.= '<td></td>';
        $header.= '</tr>';
        return $header;
    }

    private function get_group_distribution_header() : string 
    {
        return '<tr><td colspan="4">'.get_string('group_distribution', 'coursework').'</td></tr>';
    }

    private function get_all_participants_row() : string 
    {
        $row = '<tr>';
        $row = '<td>'.get_string('all_participants', 'coursework').'</td>';
        $row = '<td>';


        $row.= '</tr>';
        return $row;
    }




}


