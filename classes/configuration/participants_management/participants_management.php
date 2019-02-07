<?php

require_once 'participants_management_database_event_handler.php';

/**
 * 
 * @param stdClass $course - record of course Moodle database table
 * @param stdClass $cm - record of course_modules Moodle database table
 * @param array $groups - records of coursework_groups Moodle database table
 * @param array $tutors - records of coursework_tutors Moodle database table
 * @return string - gui of coursework configuration
 * @author Denis Makouski (Reiko)
 */
class ParticipantsManagement
{

    private $course;
    private $cm;

    private $courseworkGroups;
    private $courseworkTutors;

    private $allGroups;
    private $allTutors;
    private $allCourses;

    function __construct($course, $cm)
    {
        // Init necessary for database processing params
        $this->course = $course;
        $this->cm = $cm;

        // Process database events
        $this->handle_database_events();

        // Init other params
        $this->courseworkGroups = cw_get_coursework_groups($this->cm->instance, $this->course->id);
        $this->courseworkTutors = cw_get_tutors($this->cm->instance);

        $this->allCourses = cw_get_all_courses();
        $this->allGroups = cw_get_all_course_groups($this->course->id);
        $this->allTutors = $this->get_all_course_tutors();
    }

    private function handle_database_events() : void
    {
        $event = optional_param(DB_EVENT, 0 , PARAM_TEXT);

        if($event)
        {
            $handler = new ParticipantsManagementDatabaseEventHandler($this->course, $this->cm);
            $handler->execute();
        }
    }

    private function get_all_course_tutors() : array 
    {
        $tutors = array();
        $tutorArchetypeRoles = cw_get_archetype_roles(array('editingteacher', 'teacher'));
        $tutors = cw_get_coursework_users_with_archetype_roles($tutorArchetypeRoles, $this->course->id, $this->cm->instance);
        return $tutors;
    }

    // Public function
    public function execute() : string
    {
        return $this->get_gui();
    }

    // General gui functions
    private function get_gui() : string
    {
        $str = $this->get_participants_management_header();
        $str = $this->get_start_of_enroll_form();
        $str.= $this->get_group_selection_panel();
        $str.= $this->get_quota_left_label();
        $str.= $this->get_tutor_selection_panel();
        $str.= $this->get_add_tutor_html_button();
        $str.= $this->get_end_of_enroll_form();
        $str.= $this->get_hidden_data_for_js();

        return $str;
    }

    private function get_participants_management_header() : string
    {
        return '<h2>'.get_string('configurate_coursework', 'coursework').'</h2>';
    }

    private function get_start_of_enroll_form() : string
    {
        return '<form id="enroll_form">';
    }

    private function get_group_selection_panel() : string
    {
        $str = '<h3>'.get_string('select_groups', 'coursework').'</h3>';

        $str.= '<select name="'.GROUPS.'[]" multiple required autocomplete="off" onchange="count_members()">';
        foreach($this->allGroups as $group)
        {
            $str.= '<option class="group" value="'.$group->id.'" data-count="'.$group->studentsCount.'" ';

            if($this->is_group_selected($group)) $str .= ' selected data-initial="true"';
            else $str .= ' data-initial="false"';

            $str.= '>'.$group->name.'</option>';
        }
        $str.= '</select>';

        return $str;
    }

    private function is_group_selected($selectedGroup) : bool
    {
        foreach($this->courseworkGroups as $group)
        {
            if($group->id == $selectedGroup->id) return true;
        }

        return false;
    }

    private function get_quota_left_label() : string
    {
        $quotaLeft = 0;
        foreach($this->allGroups as $group) if($this->is_group_selected($group)) $quotaLeft += $group->studentsCount;
        foreach($this->courseworkTutors as $tutor) $quotaLeft -= $tutor->quota;

        return '<h3>'.get_string('quota_left', 'coursework').'<span id="quota_left">'.$quotaLeft.'</span></h3>';
    }

    private function get_tutor_selection_panel() : string
    {
        $count = count($this->courseworkTutors);

        $str = '<table id="tutors_table" data-rows="'.$count.'">';

        $i = 0;
        foreach($this->courseworkTutors as $cwTutor)
        {
            $str.= '<tr data-index="'.$i.'" >';
            $str.= '<td>'.$this->get_tutor_html_select($cwTutor).'</td>';
            $str.= '<td>'.$this->get_course_html_select($cwTutor->course).'</td>';
            $str.= '<td>'.$this->get_tutor_quota_html_input($cwTutor->quota).'</td>';
            $str.= '<td>'.$this->get_tutor_delete_html_button($cwTutor->id).'</td>';
            $str.= '</tr>';
            $i++;
        }

        $str.= '</table>';
        return $str;
    }

    private function get_tutor_html_select($selectedTutor) : string
    {
        $str = '<input type="hidden" name="'.COURSEWORK.TUTORS.ID.'[]" value="'.$selectedTutor->id.'" >';


        $str.= '<select name="'.TUTORS.'[]" style="width:250px;" autocomplete="off" required >';
        foreach($this->allTutors as $tutor)
        {
            $str.= '<option value="'.$tutor->id.'" ';
            if($tutor->id == $selectedTutor->tutor) $str .= ' selected ';
            $str.= ' >'.$tutor->fullname.'</option>';
        }
        $str.= '</select>';

        return $str;
    }

    private function get_course_html_select($course) : string
    {
        $str = '<select name="'.COURSES.'[]" style="width:250px;" autocomplete="off" required >';
        foreach($this->allCourses as $value)
        {
            $str.= '<option value="'.$value->id.'" ';
            if($value->id == $course) $str .= ' selected ';
            $str.= ' >'.$value->fullname.'</option>';
        }
        $str.= '</select>';

        return $str;
    }

    private function get_tutor_quota_html_input($quota) : string
    {
        $str = '<input class="quotas" type="number" ';
        $str.= ' name="'.QUOTAS.'[]" value="'.$quota.'" ';
        $str.= ' style="width:50px;" onchange="count_members()" ';
        $str.= ' autocomplete="off" required min="1" >';
        return $str;
    }

    private function get_tutor_delete_html_button($rowID) : string
    {
        return '<button onclick="return delete_tutor('.$rowID.')">'.get_string('delete', 'coursework').'</button>';
    }

    private function get_add_tutor_html_button() : string
    {
        return '<button onclick="add_tutor()">'.get_string('add_tutor', 'coursework').'</button>';
    }

    private function get_end_of_enroll_form()
    {
        $str = '<button onclick="return submit_form()">'.get_string('save_changes', 'coursework').'</button>';
        $str.= '<input type="hidden" name="id" value="'.$this->cm->id.'" >';
        $str.= '<input type="hidden" name="'.DB_EVENT.'" value="'.DB_EVENT.'" >';
        $str.= '<input type="hidden" name="'.CONFIG_MODULE.'" value="'.PARTICIPANTS_MANAGEMENT.'">';
        $str .= '</form>';
        return $str;
    }

    private function get_hidden_data_for_js() : string
    {
        $str = '';
        $str.= $this->get_all_tutors_js_hidden_data();
        $str.= $this->get_all_courses_js_hidden_data();
        return $str;
    }

    private function get_all_tutors_js_hidden_data() : string 
    {
        $str = '';
        foreach($this->allTutors as $tutor)
        {
            $str .= $this->get_hidden_element('tutors', $tutor->id, $tutor->fullname);
        }
        return $str; 
    }

    private function get_all_courses_js_hidden_data() : string 
    {
        $str = '';
        foreach($this->allCourses as $course)
        {
            $str .= $this->get_hidden_element('courses', $course->id, $course->fullname);
        }
        return $str; 
    }

    private function get_hidden_element($class, $id, $name) : string
    {
        return '<p class="hidden '.$class.'" data-id="'.$id.'" data-name="'.$name.'" ></p>';
    }



}


