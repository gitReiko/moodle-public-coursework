<?php 

namespace Coursework\View\StudentsWorksList\StudentsTable;

use Coursework\Lib\TeacherNotifications;

class NotificationRow 
{
    private $student;
    private $ntfs;

    private $moreClass;

    function __construct(\stdClass $student, TeacherNotifications $ntfs) 
    {
        $this->student = $student;
        $this->ntfs = $ntfs;

        $this->moreClass = Main::get_more_details_class($this->student->id);
    }

    public function get() : string 
    {
        $attr = array('class' => $this->moreClass.' hidden');
        $row = \html_writer::start_tag('tr', $attr);
        $row.= Main::get_indent_from_blank_cells();
        $row.= $this->get_notifications_cell();
        $row.= \html_writer::end_tag('tr');

        return $row;
    }

    private function get_notifications_cell() : string 
    {
        $notifications = $this->ntfs->get_notifications();

        if($this->is_notifications_exist($notifications))
        {
            return $this->get_list_of_notifications_cell($notifications);
        }
        else 
        {
            return $this->get_no_notifications_cell();
        }
    }

    private function is_notifications_exist($notifications) : bool 
    {
        if(count($notifications))
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    private function get_list_of_notifications_cell(array $notifications) : string 
    {
        $attr = array('class' => 'red-bg', 'colspan' => '7');

        $text = '';
        foreach ($notifications as $notification) 
        {
            $text.= \html_writer::tag('p', $notification);
        }

        return \html_writer::tag('td', $text, $attr);
    }

    private function get_no_notifications_cell() : string 
    {
        $attr = array('colspan' => '7');
        $text = get_string('no_notifications', 'coursework');
        return \html_writer::tag('td', $text, $attr);
    }



}
