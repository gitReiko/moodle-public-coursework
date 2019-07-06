<?php

require_once 'leaders_setting_database_event_handler.php';

/**
 * This class is designed to customize coursework leaders.
 * 
 * @param stdClass $course - instance of course db table
 * @param stdClass $cm - instance of course_modules db table
 * @param array $cwTeachers - all teachers of certain coursework activity - array(stdClass(id, course, quota, firstname, lastname))
 * @param array $allTeachers - all teachers of certain course - array(stdClass(id, firstname, lastname, fullname))
 * @param array $allCourses - all courses of moodle site - array(stdClass(id, fullname))
 * 
 * @author Denis Makouski
 */
class LeadersSetting
{
    private $course;
    private $cm;
    private $cwTeachers;
    private $allTeachers;
    private $allCourses;

    /** 
     * LeadersSetting class constructor.
     * 
     * @param stdClass $course - instance of course db table
     * @param stdClass $cm - instance of course_modules db table
    */
    function __construct($course, $cm)
    {
        // Init necessary for database processing params
        $this->course = $course;
        $this->cm = $cm;

        // Process database events
        $this->handle_database_events();

        // Init other params
        $this->cwTeachers = cw_get_teachers($this->cm->instance);
        $this->allCourses = cw_get_all_courses();
        $this->allTeachers = $this->get_all_course_teachers();
    }

    /**
     * Checks for the database event and, if exists, launches event handler.
     */
    private function handle_database_events() : void
    {
        $event = optional_param(DB_EVENT, 0 , PARAM_TEXT);

        if($event)
        {
            $handler = new LeadersSettingDatabaseEventHandler($this->course, $this->cm);
            $handler->execute();
        }
    }

    /**
     * Returns all course teachers.
     * 
     * @return array of all course teachers array(stdClass(id, firstname, lastname, fullname))
     */
    private function get_all_course_teachers() : array 
    {
        $allRolesOfTeachersArchetypes = cw_get_archetype_roles(array('editingteacher', 'teacher'));
        $allCourseGroups = groups_get_all_groups($this->course->id);
        return cw_get_users_with_archetype_roles_from_group($allCourseGroups, $allRolesOfTeachersArchetypes, $this->course->id, $this->cm->instance);
    }

    /**
     * Class access function.
     * 
     * @return string gui of leaders setting.
     */
    public function execute() : string
    {
        return $this->get_gui();
    }

    /**
     * Returns leaders setting gui.
     * 
     * @return string gui of leaders setting.
     */
    private function get_gui() : string
    {
        $str = $this->get_header();
        $str = $this->get_html_form_start();
        $str.= $this->get_leaders_setting_table();
        $str.= $this->get_add_teacher_button();
        $str.= $this->get_html_form_end();
        $str.= $this->get_hidden_data_for_js();

        return $str;
    }

    /**
     * Returns header of leaders setting gui.
     * 
     * @return string header of leaders setting gui.
     */
    private function get_header() : string
    {
        return '<h2>'.get_string('configurate_coursework', 'coursework').'</h2>';
    }

    /**
     * Returns start of html form.
     * 
     * @return string start of html form.
     * 
     * @todo change id of form.
     */
    private function get_html_form_start() : string
    {
        return '<form id="enroll_form">';
    }

    /**
     * Returns leaders setting panel.
     * 
     * @return string leaders setting panel.
     */
    private function get_leaders_setting_table() : string
    {
        $count = count($this->cwTeachers);

        $str = '<table id="tutors_table" data-rows="'.$count.'">';
        $str.= $this->get_leaders_setting_table_header();

        $i = 0;
        foreach($this->cwTeachers as $cwTeacher)
        {
            $str.= '<tr data-index="'.$i.'" >';
            $str.= '<td>'.$this->get_teacher_html_select($cwTeacher).'</td>';
            $str.= '<td>'.$this->get_course_html_select($cwTeacher->course).'</td>';
            $str.= '<td>'.$this->get_teacher_quota_html_input($cwTeacher->quota).'</td>';
            $str.= '<td>'.$this->get_teacher_delete_html_button($cwTeacher->id).'</td>';
            $str.= '</tr>';
            $i++;
        }

        $str.= '</table>';
        return $str;
    }

    /**
     * Returns leaders setting panel header.
     * 
     * @return string leaders setting panel header.
     */
    private function get_leaders_setting_table_header() : string 
    {
        $str = '<tr>';
        $str.= '<td>'.get_string('leader', 'coursework').'</td>';
        $str.= '<td>'.get_string('course', 'coursework').'</td>';
        $str.= '<td>'.get_string('quota', 'coursework'). '</td>';
        $str.= '<td></td>';
        $str.= '</tr>';
        return $str;
    }

    /**
     * Returns html select element with all course teachers.
     * 
     * @param stdClass $selectedTeacher instance of coursework_teachers table.
     * 
     * @return string of html select element with all course teachers.
     */
    private function get_teacher_html_select(stdClass $selectedTeacher) : string
    {
        $str = '<input type="hidden" name="'.COURSEWORK.TEACHERS.ID.'[]" value="'.$selectedTeacher->id.'" >';


        $str.= '<select name="'.TEACHERS.'[]" style="width:250px;" autocomplete="off" required >';
        foreach($this->allTeachers as $teacher)
        {
            $str.= '<option value="'.$teacher->id.'" ';
            if($teacher->id == $selectedTeacher->teacher) $str .= ' selected ';
            $str.= ' >'.$teacher->fullname.'</option>';
        }
        $str.= '</select>';

        return $str;
    }

    /**
     * Returns html select element with all site courses.
     * 
     * @param int $course id of course chosen by the teacher (stored in coursework_teachers db table)
     * 
     * @return string html select element with all site courses.
     */
    private function get_course_html_select(int $course) : string
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

    /**
     * Returns html input with teacher quota.
     * 
     * @param int $quota number of students with whom the teacher setting works
     * 
     * @return string html input with teacher quota.
     */
    private function get_teacher_quota_html_input(int $quota) : string
    {
        $str = '<input class="quotas" type="number" ';
        $str.= ' name="'.QUOTAS.'[]" value="'.$quota.'" ';
        $str.= ' style="width:50px;" onchange="count_members()" ';
        $str.= ' autocomplete="off" required min="1" >';
        return $str;
    }

    /**
     * Returns html button which calls delete function of teacher setting row.
     * 
     * @param int $rowID id of row with teacher setting.
     * 
     * @return string html button which delete teacher setting row.
     */
    private function get_teacher_delete_html_button(int $rowID) : string
    {
        return '<button onclick="return delete_tutor('.$rowID.')">'.get_string('delete', 'coursework').'</button>';
    }

    /**
     * Returns html button which add new teacher setting row.
     * 
     * @return string html button which add teacher setting row.
     */
    private function get_add_teacher_button() : string
    {
        return '<button onclick="add_tutor()">'.get_string('add_tutor', 'coursework').'</button>';
    }

    /** 
     * Returns end of html form with hidden inputs wich contains neccessary data.
     * 
     * @return string end of html form with hidden inputs wich contains neccessary data.
    */
    private function get_html_form_end()
    {
        $str = '<button>'.get_string('save_changes', 'coursework').'</button>';
        $str.= '<input type="hidden" name="id" value="'.$this->cm->id.'" >';
        $str.= '<input type="hidden" name="'.DB_EVENT.'" value="'.DB_EVENT.'" >';
        $str.= '<input type="hidden" name="'.CONFIG_MODULE.'" value="'.LEADERS_SETTING.'">';
        $str .= '</form>';
        return $str;
    }

    /**
     * Returns hidden data neccessary for configuration.js work.
     * 
     * @return string hidden data neccessary for configuration.js work.
     */
    private function get_hidden_data_for_js() : string
    {
        $str = '';
        $str.= $this->get_all_teachers_js_hidden_data();
        $str.= $this->get_all_courses_js_hidden_data();
        return $str;
    }

    /**
     * Returns hidden data neccessary for configuration.js work which contains all course teachers.
     * 
     * @return string hidden data neccessary for configuration.js work which contains all course teachers.
     */
    private function get_all_teachers_js_hidden_data() : string 
    {
        $str = '';
        foreach($this->allTeachers as $teacher)
        {
            $str .= $this->get_hidden_element('tutors', $teacher->id, $teacher->fullname);
        }
        return $str; 
    }

    /**
     * Returns hidden data neccessary for configuration.js work which contains all site courses.
     * 
     * @return string hidden data neccessary for configuration.js work which contains all site courses.
     */
    private function get_all_courses_js_hidden_data() : string 
    {
        $str = '';
        foreach($this->allCourses as $course)
        {
            $str .= $this->get_hidden_element('courses', $course->id, $course->fullname);
        }
        return $str; 
    }

    /**
     * Returns hidden html element which contains data.
     * 
     * @param string $className
     * @param int $id of any data.
     * @param string $name any string information for example course name.
     * 
     * @return string hidden html element which contains data.
     */
    private function get_hidden_element(string $className, $id, $name) : string
    {
        return '<p class="hidden '.$className.'" data-id="'.$id.'" data-name="'.$name.'" ></p>';
    }



}


