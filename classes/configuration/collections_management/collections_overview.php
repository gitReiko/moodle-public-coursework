<?php


class CollectionsOverview
{
    private $course;
    private $cm;

    //private $cwLeaders;

    function __construct(stdClass $course, stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        //$this->cwLeaders = $this->get_coursework_leaders();
    }

    public function get_gui() : string 
    {
        $gui = $this->get_overview_header();
        /*
        if($this->is_coursework_has_leaders())
        {
            $gui.= $this->get_coursework_leaders_table();
        }
        */

        $gui.= $this->get_add_leader_button();

        return $gui;
    }

    private function get_overview_header() : string 
    {
        return '<h3>'.get_string('collections_list', 'coursework').'</h3>';
    }

    /*
    private function get_coursework_leaders()
    {
        $leaders = cw_get_coursework_teachers($this->cm->instance);
        cw_add_fullnames_to_users_array($leaders);
        return $leaders;
    }

    private function is_coursework_has_leaders() : bool
    {
        if(empty($this->cwLeaders)) return false;
        else return true;
    }

    private function get_coursework_leaders_table() : string 
    {
        $table = '<h3>'.get_string('leaders_overview_table_header', 'coursework').'</h3>';
        $table.= '<table class="leaders_overview">';
        $table.= $this->get_coursework_leaders_table_header();
        $table.= $this->get_coursework_leaders_table_body();
        $table.= '</table>';
        return $table;
    }

    private function get_coursework_leaders_table_header() : string 
    {
        $header = '<tr class="header">';
        $header.= '<td>'.get_string('leader', 'coursework').'</td>';
        $header.= '<td>'.get_string('course', 'coursework').'</td>';
        $header.= '<td>'.get_string('quota', 'coursework'). '</td>';
        $header.= '<td></td>';
        $header.= '<td></td>';
        $header.= '</tr>';
        return $header;
    }

    private function get_coursework_leaders_table_body() : string 
    {
        $body = '';

        foreach($this->cwLeaders as $leader)
        {
            $body.= '<tr>';
            $body.= '<td>'.$leader->fullname.'</td>';
            $body.= '<td>'.$leader->coursename.'</td>';
            $body.= '<td align="center">'.$leader->quota.'</td>';
            $body.= '<td>'.$this->get_edit_button($leader).'</td>';
            $body.= '<td>'.$this->get_delete_button($leader->id).'</td>';
        }

        return $body;
    }

    private function get_edit_button(stdClass $leader) : string 
    {
        $button = '<form>';
        $button.= '<input type="submit" value="'.get_string('edit', 'coursework').'">';
        $button.= '<input type="hidden" name="id" value="'.$this->cm->id.'" >';
        $button.= '<input type="hidden" name="'.CONFIG_MODULE.'" value="'.LEADERS_SETTING.'">';
        $button.= '<input type="hidden" name="'.LeadersSetting::GUI_TYPE.'" value="'.LeadersSetting::EDIT_LEADER.'">';
        $button.= '<input type="hidden" name="'.TEACHER.ID.'" value="'.$leader->teacher.'">';
        $button.= '<input type="hidden" name="'.COURSE.ID.'" value="'.$leader->course.'">';
        $button.= '<input type="hidden" name="'.QUOTA.ID.'" value="'.$leader->quota.'">';
        $button.= '<input type="hidden" name="'.LeadersSetting::LEADER_ROW_ID.'" value="'.$leader->id.'">';
        $button.= '</form>';
        return $button;
    }

    private function get_delete_button(int $id) : string 
    {
        $button = '<form>';
        $button.= '<input type="submit" value="'.get_string('delete', 'coursework').'">';
        $button.= '<input type="hidden" name="'.ID.'" value="'.$this->cm->id.'" >';
        $button.= '<input type="hidden" name="'.CONFIG_MODULE.'" value="'.LEADERS_SETTING.'">';
        $button.= '<input type="hidden" name="'.LeadersSetting::GUI_TYPE.'" value="'.LeadersSetting::OVERVIEW.'">';
        $button.= '<input type="hidden" name="'.LeadersSetting::DATABASE_EVENT.'" value="'.LeadersSetting::DELETE_LEADER.'">';
        $button.= '<input type="hidden" name="'.LeadersSetting::LEADER_ROW_ID.'" value="'.$id.'">';
        $button.= '</form>';
        return $button;
    }

    */
    private function get_add_leader_button() : string 
    {
        $button = '<form>';
        $button.= '<input type="submit" value="'.get_string('add_collection', 'coursework').'">';
        $button.= '<input type="hidden" name="id" value="'.$this->cm->id.'" >';
        $button.= '<input type="hidden" name="'.CONFIG_MODULE.'" value="'.COLLECTIONS_MANAGEMENT.'">';
        $button.= '<input type="hidden" name="'.LeadersSetting::GUI_TYPE.'" value="'.CollectionsManagement::ADD_COLLECTION.'">';
        $button.= '</form>';
        return $button;
    }
    

}


