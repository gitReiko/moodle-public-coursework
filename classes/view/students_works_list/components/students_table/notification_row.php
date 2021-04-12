<?php 

namespace Coursework\View\StudentsWorksList\StudentsTable;

use Coursework\Lib\Notifications;

class NotificationRow 
{
    private $student;
    private $ntfs;

    private $moreClass;

    function __construct(\stdClass $student, Notifications $ntfs) 
    {
        $this->student = $student;
        $this->ntfs = $ntfs;

        $this->moreClass = Main::get_more_details_class($this->student->id);
    }

    public function get() : string 
    {
        $attr = array('class' => $this->moreClass.' hidden');
        $row = \html_writer::start_tag('tr', $attr);
        $row.= $this->get_empty_cell();
        $row.= $this->get_empty_cell();
        $row.= $this->get_notifications_list_cell();
        $row.= \html_writer::end_tag('tr');

        return $row;
    }

    private function get_empty_cell() : string 
    {
        $attr = array('class' => 'no-borders');
        $text = '';
        return \html_writer::tag('td', $text, $attr);
    }

    private function get_notifications_list_cell() : string 
    {
        $text = '';
        $notifications = $this->ntfs->get_notifications();

        if(count($notifications))
        {
            $attr = array(
                'class' => 'red-bg',
                'colspan' => '5'
            );

            foreach ($notifications as $notification) 
            {
                $text.= \html_writer::tag('p', $notification);
            }
        }
        else 
        {
            $attr = array(
                'colspan' => '5'
            );
            $text = get_string('no_notifications', 'coursework');
        }
        
        return \html_writer::tag('td', $text, $attr);
    }



}
