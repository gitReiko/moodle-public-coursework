<?php

require_once 'data_getters/students_works_getter.php';

use coursework_lib as lib;
use view_lib as view;

class StudentsWorksMain 
{
    private $course;
    private $cm;

    private $works;
    private $maxTaskSectionsCount;

    function __construct(stdClass $course, stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $getter = new StudentsWorksGetter($this->course, $this->cm);
        $this->works = $getter->get_students_works();

        if(view\is_coursework_use_task($this->cm))
        {
            $this->maxTaskSectionsCount = $this->get_max_sections_count();
        }
    }

    public function get_page() : string 
    {
        $page = $this->get_page_header();
        $page.= $this->get_students_list();
        $page.= lib\get_back_to_course_button($this->course->id);
        return $page;
    }

    private function get_max_sections_count()
    {
        $maxCount = 0;
        foreach($this->works as $work)
        {
            if(isset($work->sections))
            {
                $sectionsCount = count($work->sections);

                if($maxCount < $sectionsCount)
                {
                    $maxCount = $sectionsCount;
                }
            }
        }
        return $maxCount;
    }

    private function get_page_header() : string 
    {
        return '<h3>'.get_string('student_works_list_header', 'coursework').'</h3>';
    }

    private function get_students_list() : string 
    {
        $list = '<table class="students_works">';
        $list.= $this->get_students_list_header();
        $list.= $this->get_students_list_body();
        $list.= '</table>';
        return $list;
    }

    private function get_students_list_header() : string 
    {
        $header = '<thead><tr>';
        $header.= $this->get_student_header();
        $header.= $this->get_leader_header();
        $header.= $this->get_course_header();
        $header.= $this->get_theme_header();

        if(view\is_coursework_use_task($this->cm))
        {
            $header.= $this->get_task_header();

            if(!empty($this->maxTaskSectionsCount))
            {
                $header.= $this->get_task_sections_header();
            }
        }

        $header.= $this->get_work_header();
        $header.= $this->get_grade_header();
        $header.= '<td></td>';
        $header.= '</tr></thead>';
        return $header;
    }

    private function get_student_header() : string 
    {
        return '<td>'.get_string('student', 'coursework').'</td>';
    }

    private function get_leader_header() : string 
    {
        return '<td title="'.get_string('leader', 'coursework').'">'.get_string('leader_short', 'coursework').'</td>';
    }

    private function get_course_header() : string 
    {
        return '<td title="'.get_string('course', 'coursework').'">'.get_string('course_short', 'coursework').'</td>';
    }

    private function get_theme_header() : string 
    {
        return '<td title="'.get_string('theme', 'coursework').'">'.get_string('theme_short', 'coursework').'</td>';
    }

    private function get_task_header() : string 
    {
        return '<td title="'.get_string('task', 'coursework').'">'.get_string('task_short', 'coursework').'</td>';
    }

    private function get_task_sections_header() : string 
    {
        return '<td colspan="'.$this->maxTaskSectionsCount.'" title="'.get_string('sections', 'coursework').'">'.get_string('sections_short', 'coursework').'</td>';
    }

    private function get_work_header() : string 
    {
        return '<td title="'.get_string('work', 'coursework').'">'.get_string('work_short', 'coursework').'</td>';
    }

    private function get_grade_header() : string 
    {
        return '<td title="'.get_string('grade', 'coursework').'">'.get_string('grade_short', 'coursework').'</td>';
    }

    private function get_students_list_body() : string 
    {
        $body = '';
        foreach($this->works as $work)
        {
            $body.= '<tr>';
            $body.= $this->get_student_body_cell($work);
            $body.= $this->get_teacher_body_cell($work);
            $body.= $this->get_course_body_cell($work);
            $body.= $this->get_theme_body_cell($work);

            if(view\is_coursework_use_task($this->cm))
            {
                $body.= $this->get_task_body_cell($work);

                if(!empty($this->maxTaskSectionsCount))
                {
                    $body.= $this->get_task_sections_body_cell($work);
                }
            }
            
            $body.= $this->get_work_body_cell($work);
            $body.= $this->get_work_grade_cell($work);
            $body.= $this->get_go_to_page_cell($work);
            $body.= '</tr>';
        }
        return $body;
    }

    private function get_student_body_cell(stdClass $work) : string 
    {
        $td = "<td title='{$work->studentFullName}'>";
        $td.= cw_get_user_photo($work->studentId);
        $td.= "<span title='{$work->studentFullName}'>";
        $td.= $work->studentShortName;
        $td.= '</span>';
        $td.= '</td>';
        return $td;
    }

    private function get_teacher_body_cell(stdClass $work) : string 
    {
        if(empty($work->teacherId))
        {
            $title = get_string('not_selected', 'coursework');
        }
        else 
        {
            $title = $work->teacherFullName;
        }

        $td = "<td class='";
        if(empty($work->teacherId)) $td.= 'red-background';
        else $td.= 'green-background';
        $td.= "' title='{$title}'>";
        $td.= '</td>';
        return $td;
    }

    private function get_course_body_cell(stdClass $work) : string 
    {
        if(empty($work->courseId))
        {
            $title = get_string('not_selected', 'coursework');
        }
        else 
        {
            $title = $work->courseName;
        }

        $td = "<td class='";
        if(empty($work->courseId)) $td.= 'red-background';
        else $td.= 'green-background';
        $td.= "' title='{$title}'>";
        $td.= '</td>';
        return $td;
    }

    private function get_theme_body_cell(stdClass $work) : string 
    {
        if(empty($work->themeName))
        {
            $title = get_string('not_selected', 'coursework');
        }
        else 
        {
            $title = $work->themeName;
        }

        $td = "<td class='";
        if(empty($work->themeName)) $td.= 'red-background';
        else $td.= 'green-background';
        $td.= "' title='{$title}'>";
        $td.= '</td>';
        return $td;
    }

    private function get_task_body_cell(stdClass $work) : string 
    {
        if(empty($work->task))
        {
            $title = get_string('not_assigned', 'coursework');
        }
        else 
        {
            $title = get_string('assigned', 'coursework');
        }

        $td = "<td class='";
        if(empty($work->task)) $td.= 'red-background';
        else $td.= 'green-background';
        $td.= "' title='{$title}'>";
        $td.= '</td>';
        return $td;
    }

    private function get_task_sections_body_cell(stdClass $work) : string 
    {
        $td = '';

        $i = 0;
        if(!empty($work->sections))
        {
            foreach($work->sections as $section)
            {
                $title = $this->get_section_title($section);
                $color = $this->get_cell_color_from_status($section->status);
    
                $td.= "<td class='".$color."' title='{$title}'></td>";
    
                $i++;
            }
        }

        if($this->maxTaskSectionsCount > $i)
        {
            $td.= $this->fill_empty_task_td($i);
        }

        return $td;
    }

    private function get_section_title(stdClass $section)
    {
        $title = get_string('section', 'coursework').': ';
        $title.= $section->name.'&#013;';
        $title.= get_string('state', 'coursework').': ';
        $title.= get_string($section->status, 'coursework').'&#013;';
        $title.= get_string('modify_date', 'coursework').': ';

        if(!empty($section->timeModified))
        {
            $title.= date('d-m-Y', $section->timeModified);
        }

        return $title;
    }

    private function get_cell_color_from_status(string $status) : string 
    {
        switch($status)
        {
            case NOT_READY:
                return 'red-background';
            case READY:
                return'green-background';
            case NEED_TO_FIX:
                return 'yellow-background';
                break;
            case SENT_TO_CHECK:
                return 'blue-background';
        }
    }

    private function fill_empty_task_td(int $i) : string
    {
        $td = '';
        $title = get_string('not_assigned', 'coursework');
        $maxIterations = $this->maxTaskSectionsCount - $i;
        for($j = 0; $j < $maxIterations; $j++)
        {
            $td.= "<td class='grey-background' title='{$title}'></td>";
        }
        
        return $td;
    }

    private function get_work_body_cell(stdClass $work) : string 
    {
        $td = '';

        if(empty($work->work))
        {
            $title = get_string(NOT_READY, 'coursework');
            $color = $this->get_cell_color_from_status(NOT_READY);
        }
        else
        {
            $title = get_string($work->work, 'coursework');
            $color = $this->get_cell_color_from_status($work->work);
        }

        return "<td class='".$color."' title='{$title}'></td>";
    }

    private function get_work_grade_cell(stdClass $work) : string 
    {
        if(empty($work->grade))
        {
            return '<td class="red-background"></td>';
        }
        else
        {
            return '<td class="green-background">'.$work->grade.'</td>';
        }
    }

    private function get_go_to_page_cell(stdClass $work) : string 
    {
        $link = '/mod/coursework/view.php?id='.$this->cm->id;
        $link.= '&'.ViewMain::GUI_EVENT.'='.ViewMain::USER_WORK;
        $link.= '&'.STUDENT.ID.'='.$work->studentId;
        $td = '<td>';
        $td.= "<a href='$link' >";
        $td.= '<button>'.get_string('go_to_work', 'coursework').'</button>';
        $td.= '</a>';
        $td.= '</td>';
        return $td;
    }


}