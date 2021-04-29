<?php 

namespace Coursework\View\StudentsWorksList\StudentsTable;

use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\View\StudentsWorksList as swl;
use Coursework\Lib\TeacherNotifications;
use Coursework\Lib\Enums as enum;
use Coursework\View\Main as view_main;

class MainRow 
{
    private $cm;
    private $student;
    private $ntfs;

    private $moreClass;
    private $moreBtnId;

    function __construct(\stdClass $cm, \stdClass $student, TeacherNotifications $ntfs) 
    {
        $this->cm = $cm;
        $this->student = $student;
        $this->ntfs = $ntfs;

        $this->moreClass = Main::get_more_details_class($this->student->id);
        $this->moreBtnId = Main::get_more_details_btn_id($this->student->id);
    }

    public function get() : string 
    {
        $row = \html_writer::start_tag('tr');
        $row.= $this->get_notification_cell();
        $row.= $this->get_more_button();
        $row.= $this->get_work_cell();
        $row.= $this->get_student_cell();
        $row.= $this->get_state_cell();
        $row.= $this->get_theme_cell();
        $row.= $this->get_grade_cell();
        $row.= \html_writer::end_tag('tr');

        return $row;
    }

    private function get_notification_cell() : string
    {
        $fun = "open_close_table_row(`{$this->moreClass}`,`{$this->moreBtnId}`)";
        $attr = array(
            'class' => 'notibtn',
            'onclick' => $fun,
            'title' => get_string('show_notifications', 'coursework')
        );

        if($this->ntfs->is_notifications_exist())
        {
            $text = '<i class="fa fa-exclamation-triangle"></i>';
        }
        else 
        {
            $text = '';
        }

        return \html_writer::tag('td', $text, $attr);
    }

    private function get_more_button() : string 
    {
        $fun = "open_close_table_row(`{$this->moreClass}`,`{$this->moreBtnId}`)";
        $attr = array(
            'class' => 'morebtn', 
            'onclick' => $fun,
            'title' => get_string('show_more_info', 'coursework')
        );
        $text = "<i class='fa fa-arrow-down' id='{$this->moreBtnId}'></i>";
        return \html_writer::tag('td', $text, $attr);
    }

    private function get_work_cell() : string 
    {
        $attr = array(
            'href' => $this->get_go_to_work_url($this->student),
            'title' => get_string('go_to_student_work', 'coursework')
        );
        $text = get_string('work', 'coursework');
        $a = \html_writer::tag('a', $text, $attr);
        return \html_writer::tag('td', $a);
    }

    private function get_go_to_work_url()
    {
        $url = '/mod/coursework/view.php';
        $url.= '?'.view_main::ID.'='.$this->cm->id;
        $url.= '&'.view_main::GUI_EVENT.'='.view_main::USER_WORK;
        $url.= '&'.view_main::STUDENT_ID.'='.$this->student->id;

        return $url;
    }

    private function get_student_cell() : string 
    {
        $text = cg::get_user_photo($this->student->id).' ';

        global $COURSE;
        $url = '/user/view.php?id='.$this->student->id;
        $url.= '&course='.$COURSE->id;
        $attr = array('href' => $url);
        $name = $this->student->lastname.' '.$this->student->firstname;
        $text.= \html_writer::tag('a', $name, $attr);

        return \html_writer::tag('td', $text);
    }

    private function get_state_cell() : string 
    {
        $text = cg::get_state_name($this->student->status);
        return \html_writer::tag('td', $text);
    }

    private function get_theme_cell() : string 
    {
        $text = $this->student->theme;
        return \html_writer::tag('td', $text);
    }

    private function get_grade_cell() : string 
    {
        $attr = array('class' => 'center');

        if(empty($this->student->grade))
        {
            $text = '';
        }
        else 
        {
            $text = $this->student->grade;
        }

        return \html_writer::tag('td', $text, $attr);
    }




}
