<?php

// Известные баги
// 1) Если в момент обновления базы данных, в данных будет несколько записей
// с одинаковым преподавателей и курсом, то:вторая и последующие записи будут
// обновлять первую.

class ParticipantsManagement
{

    private $course;
    private $cm;

    private $tutorRowID;
    private $groups;
    private $tutors;
    private $courses;
    private $quotas;

    private $allGroups;
    private $allTutors;
    private $allCourses;


    // Constructor functions
    function __construct($course, $cm)
    {
        global $PAGE;

        if (has_capability('mod/coursework:enrollmembers', $PAGE->cm->context))
        {
            $this->course = $course;
            $this->cm = $cm;

            $this->handle_db_event();

            $this->groups = $this->get_groups();
            $this->initilize_tutors_arrays();

            $this->allGroups = $this->get_all_groups();
            $this->handle_groups_members();
            $this->allCourses = $this->get_all_courses();
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

    private function initilize_tutors_arrays() : void
    {
        global $DB;

        $rows = $DB->get_records('coursework_tutors', array('coursework'=>$this->cm->instance));

        $this->tutorRowID = array();
        $this->tutors = array();
        $this->courses = array();
        $this->quotas = array();

        foreach($rows as $row)
        {
            $this->tutorRowID[] = $row->id;
            $this->tutors[] = $row->tutor;
            $this->courses[] = $row->course;
            $this->quotas[] = $row->quota;
        }
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
        global $PAGE;

        if (has_capability('mod/coursework:enrollmembers', $PAGE->cm->context))
        {
            return $this->gui_display();
        }
        else return $this->gui_no_permission();
    }

    // DB functions
    private function handle_db_event() : void
    {
        $event = optional_param(ECM_DATABASE, 0 , PARAM_TEXT);

        if($event)
        {
            $this->handle_groups_table();
            $this->handle_tutors_table();
        }
    }

    private function handle_groups_table() : void
    {
        $groups = optional_param_array(ECM_GROUPS, array(), PARAM_INT);

        foreach($groups as $group)
        {
            if(!$this->is_groups_row_exists($group))
            {
                $this->insert_groups_row($group);
            }
        }

        $this->clear_groups_rows($groups);
    }

    private function is_groups_row_exists($group) : bool
    {
        global $DB;

        $conditions = array('coursework'=>$this->cm->instance, 'groupid' => $group);

        if($DB->record_exists('coursework_groups', $conditions)) return true;
        else return false;
    }

    private function insert_groups_row($group) : void
    {
        if($group)
        {
            global $DB;

            $temp = new stdClass;
            $temp->coursework = $this->cm->instance;
            $temp->groupid = $group;

            $DB->insert_record('coursework_groups', $temp, false);
        }
        else
        {
            echo get_string('error_no_group', 'coursework');
        }
    }

    private function clear_groups_rows($groups) : void  // Не реализовано удаление студентов, зависимых от группы
    {
        global $DB;

        $rows = $DB->get_records('coursework_groups', array('coursework'=>$this->cm->instance));

        foreach($rows as $row)
        {
            $is_used = false;

            foreach($groups as $group)
            {
                if($row->groupid == $group) $is_used = true;
            }

            if(!$is_used)
            {
                $DB->delete_records('coursework_groups', array('id'=>$row->id));
            }
        }
    }

    private function handle_tutors_table() : void
    {
        $tutors = optional_param_array(ECM_TUTORS, array(), PARAM_INT);
        $courses = optional_param_array(ECM_COURSES, array(), PARAM_INT);
        $quotas = optional_param_array(ECM_QUOTA, array(), PARAM_INT);

        for($i = 0; $i < count($tutors); $i++)
        {
            if($this->is_tutors_row_exists($tutors[$i], $courses[$i]))
            {
                $this->update_tutors_row($tutors[$i], $courses[$i], $quotas[$i]); // PROVERIT' !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            }
            else
            {
                $this->insert_tutors_row($tutors[$i], $courses[$i], $quotas[$i]);
            }
        }

        $del = optional_param(ECM_DEL_TUTOR, 0, PARAM_INT);
        if($del) $this->delete_tutors_row($del);
    }

    private function is_tutors_row_exists($tutor, $course) : bool
    {
        global $DB;

        $conditions = array('coursework'=>$this->cm->instance, 'tutor' => $tutor, 'course' => $course);

        if($DB->record_exists('coursework_tutors', $conditions)) return true;
        else return false;
    }

    private function insert_tutors_row($tutor, $course, $quota) : void
    {
        if($tutor && $course && $quota)
        {
            global $DB;

            $temp = new stdClass;
            $temp->coursework = $this->cm->instance;
            $temp->tutor = $tutor;
            $temp->course = $course;
            $temp->quota = $quota;

            $DB->insert_record('coursework_tutors', $temp, false);
        }
        else
        {
            echo get_string('error_no_tutor_course_quota', 'coursework');
        }
    }

    private function update_tutors_row($tutor, $course, $quota) : void
    {
        global $DB;

        $row = $DB->get_record('coursework_tutors', array('coursework'=>$this->cm->instance,'tutor'=>$tutor,'course'=>$course));

        $temp = new stdClass;
        $temp->id = $row->id;
        $temp->coursework = $this->cm->instance;
        $temp->tutor = $tutor;
        $temp->course = $course;
        $temp->quota = $quota;

        $DB->update_record('coursework_tutors', $temp);
    }

    private function delete_tutors_row($rowID) : void
    {
        global $DB;
        $DB->delete_records('coursework_tutors', array('id'=>$rowID));
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

        $str.= '<select name="'.ECM_GROUPS.'[]" multiple required autocomplete="off" onchange="count_members()">';
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
        foreach($this->quotas as $quota) $quotaLeft -= $quota;

        return '<h3>'.get_string('quota_left', 'coursework').'<span id="quota_left">'.$quotaLeft.'</span></h3>';
    }

    private function gui_tutors() : string
    {
        $count = count($this->tutors);

        $str = '<table id="tutors_table" data-rows="'.$count.'">';

        for($i = 0; $i < $count; $i++)
        {
            $str.= '<tr data-index="'.$i.'" >';
            $str.= '<td>'.$this->gui_tutor_select($this->tutors[$i]).'</td>';
            $str.= '<td>'.$this->gui_course_select($this->courses[$i]).'</td>';
            $str.= '<td>'.$this->gui_quota_input($this->quotas[$i]).'</td>';
            $str.= '<td>'.$this->gui_delete_btn($this->tutorRowID[$i]).'</td>';
            $str.= '</tr>';
        }

        $str.= '</table>';
        return $str;
    }

    private function gui_tutor_select($tutor) : string
    {
        $str = '<select name="'.ECM_TUTORS.'[]" style="width:250px;" autocomplete="off" required >';
        foreach($this->allTutors as $value)
        {
            $str.= '<option value="'.$value->id.'" ';
            if($value->id == $tutor) $str .= ' selected ';
            $str.= ' >'.$value->name.'</option>';
        }
        $str.= '</select>';

        return $str;
    }

    private function gui_course_select($course) : string
    {
        $str = '<select name="'.ECM_COURSES.'[]" style="width:250px;" autocomplete="off" required >';
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
        $str.= ' name="'.ECM_QUOTA.'[]" value="'.$quota.'" ';
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

    private function gui_no_permission() : string
    {
        return '<h2 class="darkred">'.get_string('no_permission', 'coursework').'</h2>';
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
        $str.= '<input type="hidden" name="'.ECM_DATABASE.'" value="'.ECM_DATABASE.'" >';
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
