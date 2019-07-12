<?php


class LeadersOverviewGUI
{
    private $course;
    private $cm;

    private $cwLeaders;

    function __construct(stdClass $course, stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->cwLeaders = $this->get_coursework_leaders();
    }

    public function get_gui() : string 
    {
        $gui = '';
        if($this->is_coursework_has_leaders())
        {
            $gui.= $this->get_coursework_leaders_table();
        }

        $gui.= $this->get_add_leader_button();

        return $gui;
    }

    private function get_coursework_leaders()
    {
        $leaders = cw_get_coursework_teachers($this->cm->instance); // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! Rename function get_coursework_teachers !!!
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
        $header = '<tr>';
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
            $body.= '<td>'.$leader->course.'</td>';
            $body.= '<td>'.$leader->quota.'</td>';
            $body.= '<td>'.$this->get_edit_button($leader->id).'</td>';
            $body.= '<td>'.$this->get_delete_button($leader->id).'</td>';
        }

        return $body;
    }

    private function get_edit_button(int $id) : string 
    {
        $button = '<form>';
        $button.= '<input type="submit" value="'.get_string('edit', 'coursework').'">';
        $button.= '</form>';
        return $button;

        // +++ params
        // Объединить через что-то с удалить
    }

    private function get_delete_button(int $id) : string 
    {
        $button = '<form>';
        $button.= '<input type="submit" value="'.get_string('delete', 'coursework').'">';
        $button.= '</form>';
        return $button;

        // +++ params
        // Объединить через что-то с редактирвоать
    }

    private function get_add_leader_button() : string 
    {
        $button = '<form>';
        $button.= '<input type="submit" value="'.get_string('add_tutor', 'coursework').'">';
        $button.= '<input type="hidden" name="id" value="'.$this->cm->id.'" >';
        $button.= '<input type="hidden" name="'.CONFIG_MODULE.'" value="'.LEADERS_SETTING.'">';
        $button.= '<input type="hidden" name="'.LeadersSetting::DATABASE_EVENT.'" value="'.LeadersSetting::ADD_LEADER.'">';
        $button.= '<input type="hidden" name="'.LeadersSetting::GUI_TYPE.'" value="'.LeadersSetting::ADD_LEADER.'">';
        $button.= '</form>';
        return $button;

        // Объединить через что-то с редактирвоать
    }

}


