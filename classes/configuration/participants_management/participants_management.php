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

    private $groups;
    private $tutors;

    private $allGroups;
    private $allTutors;
    private $allCourses;

    /**
     * First initilize necessary variables ($course, $cm)
     * Then handle database events because they can change data based on which further work takes place
     */
    function __construct($course, $cm)
    {
        // Init necessary for database processing params
        $this->course = $course;
        $this->cm = $cm;

        // Process database events
        $this->handle_database_events();

        // Init other params
        $this->groups = cw_get_groups($this->cm->instance);
        $this->tutors = cw_get_tutors($this->cm->instance);

        $this->allCourses = cw_get_all_courses();
        $this->allGroups = $this->get_all_course_groups();
        $this->allTutors = $this->get_all_course_tutors();
    }

    private function handle_database_events() : void
    {
        $event = optional_param(DB_EVENT, 0 , PARAM_TEXT);

        if($event)
        {
            $hadler = new ParticipantsManagementDatabaseEventHandler($this->course, $this->cm);
            $hadler->execute();
        }
    }

    private function get_all_course_groups() : array 
    {
        $groups = cw_get_all_course_groups($this->course->id);
        $groups = $this->add_members_count_to_groups($groups);
        return $groups;
    }

    private function add_members_count_to_groups(array $groups) : array 
    {
        $studentArchetypeRoles = cw_get_archetype_roles(array('student'));

        foreach($groups as $group)
        {
            $members = cw_get_group_members($group->id);
            $membersCount = 0;

            foreach($members as $member)
            {
                $memberRoles = get_user_roles(context_course::instance($this->course->id), $member->id);

                if(cw_is_user_archetype($memberRoles, $studentArchetypeRoles)) $membersCount++;
            }

            $group->membersCount = $membersCount++;
        }

        return $groups;
    }

    private function get_all_course_tutors() : array 
    {
        $tutors = array();
        $tutorArchetypeRoles = cw_get_archetype_roles(array('editingteacher', 'teacher'));
        foreach($this->allGroups as $group)
        {
            $members = cw_get_group_members($group->id);

            foreach($members as $member)
            {
                $memberRoles = get_user_roles(context_course::instance($this->course->id), $member->id);

                if(cw_is_user_archetype($memberRoles, $tutorArchetypeRoles))
                {
                    $tutors[] = $member;
                }
            }
        }
        return $tutors;
    }

    // Public function
    public function execute() : string
    {
        return $this->gui_display();
    }

    // General gui functions
    private function gui_display() : string
    {
        $str = $this->gui_header();
        $str = $this->gui_start_form();
        $str.= $this->gui_groups();
        $str.= $this->gui_quota_left();
        $str.= $this->gui_tutors();
        $str.= $this->gui_add_button();
        $str.= $this->gui_end_form();
        $str.= $this->gui_js_data();

        return $str;
    }

    private function gui_header() : string
    {
        return '<h2>'.get_string('configurate_coursework', 'coursework').'</h2>';
    }

    private function gui_start_form() : string
    {
        return '<form id="enroll_form">';
    }

    private function gui_groups() : string
    {
        $str = '<h3>'.get_string('select_groups', 'coursework').'</h3>';

        $str.= '<select name="'.GROUPS.'[]" multiple required autocomplete="off" onchange="count_members()">';
        foreach($this->allGroups as $group)
        {
            $str.= '<option class="group" value="'.$group->id.'" data-count="'.$group->membersCount.'" ';

            if($this->is_group_selected($group)) $str .= ' selected data-initial="true"';
            else $str .= ' data-initial="false"';

            $str.= '>'.$group->name.'</option>';
        }
        $str.= '</select>';

        return $str;
    }

    private function is_group_selected($group) : bool
    {
        foreach($this->groups as $value)
        {
            if($value->id == $group->id) return true;
        }

        return false;
    }

    private function gui_quota_left() : string
    {
        $quotaLeft = 0;
        foreach($this->allGroups as $group) if($this->is_group_selected($group)) $quotaLeft += $group->membersCount;
        foreach($this->tutors as $tutor) $quotaLeft -= $tutor->quota;

        return '<h3>'.get_string('quota_left', 'coursework').'<span id="quota_left">'.$quotaLeft.'</span></h3>';
    }

    private function gui_tutors() : string
    {
        $count = count($this->tutors);

        $str = '<table id="tutors_table" data-rows="'.$count.'">';

        $i = 0;
        foreach($this->tutors as $value)
        {
            $str.= '<tr data-index="'.$i.'" >';
            $str.= '<td>'.$this->gui_tutor_select($value).'</td>';
            $str.= '<td>'.$this->gui_course_select($value->course).'</td>';
            $str.= '<td>'.$this->gui_quota_input($value->quota).'</td>';
            $str.= '<td>'.$this->gui_delete_btn($value->id).'</td>';
            $str.= '</tr>';
            $i++;
        }

        $str.= '</table>';
        return $str;
    }

    private function gui_tutor_select($selectedTutor) : string
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

    private function gui_course_select($course) : string
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

    private function gui_quota_input($quota) : string
    {
        $str = '<input class="quotas" type="number" ';
        $str.= ' name="'.QUOTAS.'[]" value="'.$quota.'" ';
        $str.= ' style="width:50px;" onchange="count_members()" ';
        $str.= ' autocomplete="off" required min="1" >';
        return $str;
    }

    private function gui_delete_btn($rowID) : string
    {
        return '<button onclick="return delete_tutor('.$rowID.')">'.get_string('delete', 'coursework').'</button>';
    }

    private function gui_add_button() : string
    {
        return '<button onclick="add_tutor()">'.get_string('add_tutor', 'coursework').'</button>';
    }

    private function gui_js_data() : string
    {
        $str = '';

        // All course tutors
        foreach($this->allTutors as $tutor)
        {
            $str .= $this->get_hidden_element('tutors', $tutor->id, $tutor->fullname);
        }

        // All courses
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

    private function gui_end_form()
    {
        $str = '<button onclick="return submit_form()">'.get_string('save_changes', 'coursework').'</button>';
        $str.= '<input type="hidden" name="id" value="'.$this->cm->id.'" >';
        $str.= '<input type="hidden" name="'.DB_EVENT.'" value="'.DB_EVENT.'" >';
        $str.= '<input type="hidden" name="'.CONFIG_MODULE.'" value="'.PARTICIPANTS_MANAGEMENT.'">';
        $str .= '</form>';
        return $str;
    }

}


