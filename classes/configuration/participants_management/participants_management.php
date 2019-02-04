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
        $this->course = $course;
        $this->cm = $cm;

        $this->handle_database_events();

        $this->groups = $this->get_groups();
        $this->tutors = cw_get_tutor_records($this->cm->instance);

        $this->allGroups = $this->get_all_groups();
        $this->handle_groups_members();
        $this->allCourses = $this->get_all_courses();

        
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

    private function get_groups() : array
    {
        global $DB;

        $groups = $DB->get_records('coursework_groups', array('coursework'=>$this->cm->instance), '', 'groupid');

        $temp = array();
        foreach($groups as $group)
        {
            $temp[] = $group->groupid;
        }

        return $temp;
    }

    private function get_all_groups() : array
    {
        global $DB;

        return $DB->get_records('groups', array('courseid'=>$this->course->id), 'name', 'id, name');
    }

    // This function creates allTutors array and count students in the groups.
    private function handle_groups_members() : void
    {
        global $DB, $PAGE;
        $tutors = array();

        foreach($this->allGroups as $group)
        {
            $studentsCount = 0;

            $members = $DB->get_records('groups_members', array('groupid'=>$group->id),'','userid');

            foreach($members as $member)
            {
                $roles = get_user_roles(context_course::instance($this->course->id), $member->userid);

                foreach($roles as $role)
                {
                    if($role->roleid == TEACHER_ROLE
                        ||$role->roleid == EDITING_TEACHER_ROLE
                        ||$role->roleid == TUTOR_ROLE)
                    {
                        $tutors[] = $member->userid;
                    }

                    if($role->roleid == STUDENT_ROLE) $studentsCount++;
                }
            }

            $group->count = $studentsCount;
        }

        $tutors = array_unique($tutors);
        $tutors = $this->add_teacher_names($tutors);
        usort($tutors, "cmp_tutors");

        $this->allTutors = $tutors;
    }

    private function add_teacher_names($tutors) : array
    {
        $teachers = array();

        foreach($tutors as $tutor)
        {
            $temp = new stdClass;
            $temp->id = $tutor;
            $temp->name = cw_get_user_name($tutor);

            $teachers[] = $temp;
        }

        return $teachers;
    }

    private function get_all_courses() : array
    {
        global $DB;

        return $DB->get_records('course', array(), 'fullname', 'id, fullname');
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
            $str.= '<option class="group" value="'.$group->id.'" data-count="'.$group->count.'" ';

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
            if($value == $group->id) return true;
        }

        return false;
    }

    private function gui_quota_left() : string
    {
        $quotaLeft = 0;
        foreach($this->allGroups as $group) if($this->is_group_selected($group)) $quotaLeft += $group->count;
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

    private function gui_tutor_select($tutor) : string
    {
        $str = '<input type="hidden" name="'.COURSEWORK.TUTORS.ID.'[]" value="'.$tutor->id.'" >';


        $str.= '<select name="'.TUTORS.'[]" style="width:250px;" autocomplete="off" required >';
        foreach($this->allTutors as $value)
        {
            $str.= '<option value="'.$value->id.'" ';
            if($value->id == $tutor->tutor) $str .= ' selected ';
            $str.= ' >'.$value->name.'</option>';
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
            $str .= $this->get_hidden_element('tutors', $tutor->id, $tutor->name);
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


function cmp_tutors(stdClass $a, stdClass $b) : int
{
    if ($a->name == $b->name) {
        return 0;
    }
    return ($a->name < $b->name) ? -1 : 1;
}
