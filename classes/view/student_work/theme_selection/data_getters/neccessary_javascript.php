<?php 

namespace Coursework\View\StudentWork\ThemeSelection;

class NeccessaryJavascript 
{
    private $teachers;
    private $courses;
    private $themes;

    function __construct($teachers, $courses, $themes)
    {
        $this->teachers = $teachers;
        $this->courses = $courses;
        $this->themes = $themes;
    }

    public function get()
    {
        $data = $this->get_teachers_data();
        $data.= $this->get_courses_data();
        $data.= $this->get_themes_data();

        return $data;
    }

    private function get_teachers_data() : string 
    {
        $data = '';

        foreach($this->teachers as $teacher)
        {
            if($teacher->available_quota != '0')
            {
                $attr = array(
                    'class' => 'hidden teachers_and_their_courses_js_data',
                    'data-leader' => $teacher->id,
                    'data-courses' => $this->get_teacher_courses($teacher)
                );
    
                $data.= \html_writer::tag('p', '', $attr);
            }
        }

        return $data;
    }

    private function get_teacher_courses(\stdClass $teacher) : string 
    {
        $courses = '';

        foreach($teacher->courses as $course)
        {
            if($course->available_quota != '0')
            {
                $courses.= $course->id.' ';
            }
        }
        
        return mb_substr($courses, 0, -1);
    }

    private function get_courses_data() : string 
    {
        $data = '';

        foreach($this->courses as $course)
        {
            $attr = array(
                'class' => 'hidden courses_js_data',
                'data-id' => $course->id,
                'data-fullname' => $course->name
            );

            $data.= \html_writer::tag('p', '', $attr);
        }

        return $data;
    }

    private function get_themes_data() : string 
    {
        $data = '';

        foreach($this->themes as $container)
        {
            foreach($container->themes as $theme)
            {   
                $attr = array(
                    'class' => 'hidden themes_js_data',
                    'data-theme-id' => $theme->id,
                    'data-course-id' => $container->course,
                    'data-name' => $theme->content
                );
    
                $data.= \html_writer::tag('p', '', $attr);
            }
        }

        return $data;  
    }

}

