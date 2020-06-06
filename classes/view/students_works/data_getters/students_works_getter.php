<?php


use coursework_lib as lib;

class StudentsWorksGetter 
{
    private $course;
    private $cm;

    private $studentsWorks;

    function __construct(stdClass $course, stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->init_studentsWorks();
    }

    public function get_students_works()
    {
        return $this->studentsWorks;
    }

    private function init_studentsWorks()
    {
        $students = lib\get_coursework_students($this->cm);

        global $USER;
        if(lib\is_user_teacher($this->cm, $USER->id))
        {
            $students = $this->filter_none_teacher_students($students);
        }

        $works = $this->add_additional_info_to_students_array($students);

        $this->studentsWorks = $works;
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
                $newStudent->studentFullName = $student->lastname.' '.$student->firstname;
                $newStudent->studentShortName = lib\get_user_shortname(lib\get_user_from_id($student->id));
                
                if(!empty($cwStudent->teacher))
                {
                    $newStudent->teacherId = $cwStudent->teacher;
                    $teacher = lib\get_user_from_id($cwStudent->teacher);
                    $newStudent->teacherFullName = $teacher->lastname.' '.$teacher->firstname;
                }

                if(!empty($cwStudent->course))
                {
                    $newStudent->courseId = $cwStudent->course;
                    $newStudent->courseName = lib\get_course_fullname($cwStudent->course);
                }
                
                $newStudent->themeName = $this->get_theme_name($cwStudent);
                $newStudent->task = $cwStudent->task;
                $newStudent->sections = $this->get_task_sections($student->id);
                $newStudent->work = $cwStudent->status;
                $newStudent->grade = $cwStudent->grade;
                
                $newStudents[] = $newStudent;
            }
            else 
            {
                $newStudent = new stdClass;
                $newStudent->studentId = $student->id;
                $newStudent->studentFullName = $student->lastname.' '.$student->firstname;
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

    private function get_task_sections(int $studentId)
    {
        $sections = lib\get_sections_to_check($this->cm, $studentId);

        $newSections = array();
        foreach($sections as $section)
        {
            $newSection = new stdClass;
            $newSection->name = $section->name;
            $newSection->status = $this->get_section_status($studentId, $section->id);
            $newSection->timeModified = $this->get_section_timemodified($studentId, $section->id);

            $newSections[] = $newSection;
        }

        return $newSections;
    }

    private function get_section_status(int $studentId, int $sectionId) : string 
    {
        global $DB;
        $conditions = array('coursework' => $this->cm->instance,
                            'student' => $studentId,
                            'section' => $sectionId);
        $status = $DB->get_field('coursework_sections_status', 'status', $conditions);

        if(empty($status)) return NOT_READY;
        else return $status;
    }

    private function get_section_timemodified(int $studentId, int $sectionId) : string 
    {
        global $DB;
        $conditions = array('coursework' => $this->cm->instance,
                            'student' => $studentId,
                            'section' => $sectionId);
        $timeModified = $DB->get_field('coursework_sections_status', 'timemodified', $conditions);

        if(empty($timeModified)) return 0;
        else return $timeModified;
    }




}