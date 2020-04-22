<?php


use coursework_lib as lib;

class StudentsWorksGetter 
{
    private $course;
    private $cm;

    private $availableStudents;

    function __construct(stdClass $course, stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->init_available_students();
    }

    public function get_available_students()
    {
        return $this->availableStudents;
    }

    private function init_available_students()
    {
        $students = lib\get_coursework_students($this->cm);

        global $USER;
        if(lib\is_user_teacher($this->cm, $USER->id))
        {
            $students = $this->filter_none_teacher_students($students);
        }

        $students = $this->add_additional_info_to_students_array($students);

        $this->availableStudents = $students;
    }

    private function filter_none_teacher_students($students)
    {
        $filtered = array();
        foreach($students as $student)
        {
            if($this->is_student_belong_to_teacher($student->id))
            {
                $filtered[] = $student;
            }
        }
        return $filtered;
    }

    private function is_student_belong_to_teacher(int $studentId) : bool 
    {
        global $DB, $USER;
        $where = array('coursework'=>$this->cm->instance, 'student'=>$studentId, 'teacher'=>$USER->id);
        return $DB->record_exists('coursework_students', $where);
    }

    private function add_additional_info_to_students_array($students)
    {
        $newStudents = array();
        foreach($students as $student)
        {
            if($this->is_coursework_student_exist($student->id))
            {
                $cwStudent = $this->get_coursework_student($student->id);

                $newStudent = new stdClass;
                $newStudent->studentId = $student->id;
                $newStudent->studentFullName = $student->fullname;
                $newStudent->studentShortName = lib\get_user_shortname(lib\get_user_from_id($student->id));
                
                if(!empty($cwStudent->teacher))
                {
                    $newStudent->teacherId = $cwStudent->teacher;
                    $teacher = lib\get_user_from_id($cwStudent->teacher);
                    $newStudent->teacherFullName = lib\get_user_fullname($teacher);
                }

                if(!empty($cwStudent->course))
                {
                    $newStudent->courseId = $cwStudent->course;
                    $newStudent->courseName = lib\get_course_fullname($cwStudent->course);
                }
                
                $newStudent->themeName = $this->get_theme_name($cwStudent);
                
                $newStudents[] = $newStudent;
            }
            else 
            {
                $newStudent = new stdClass;
                $newStudent->studentId = $student->id;
                $newStudent->studentFullName = $student->fullname;
                $newStudent->studentShortName = lib\get_user_shortname(lib\get_user_from_id($student->id));

                $newStudents[] = $newStudent;
            }
        }
        return $newStudents;
    }

    private function is_coursework_student_exist(int $studentId)
    {
        global $DB;
        $where = array('coursework'=>$this->cm->instance, 'student'=>$studentId);
        return $DB->record_exists('coursework_students', $where);
    }

    private function get_coursework_student(int $studentId)
    {
        global $DB;
        $where = array('coursework'=>$this->cm->instance, 'student'=>$studentId);
        return $DB->get_record('coursework_students', $where);
    }

    private function get_theme_name(stdClass $theme)
    {
        if(!empty($theme->theme) || !empty($theme->owntheme))
        {
            if(empty($theme->theme))
            {
                return $theme->owntheme;
            }
            else 
            {
                return lib\get_theme_name($theme->theme);
            }
        }
    }





}