<?php 

namespace view_lib;

function get_back_to_works_list_button(\stdClass $cm) : string 
{
    $btn = '<td>';
    $btn.= '<form>';
    $btn.= '<input type="hidden" name="'.ID.'" value="'.$cm->id.'" >';
    $btn.= '<button>';
    $btn.= get_string('back_to_works_list', 'coursework');
    $btn.= '</button>';
    $btn.= '</form>';
    $btn.= '</td>';
    return $btn;
}

function is_coursework_use_task($cm) : bool 
{
    global $DB;
    $where = array('id'=>$cm->instance, 'usetask'=>1);
    return $DB->record_exists('coursework', $where);
}

function is_user_have_task($cm, $studentId) : int 
{
    global $DB;
    $sql = 'SELECT id 
            FROM {coursework_students} 
            WHERE coursework = ?
            AND student = ?
            AND (task IS NOT NULL OR task <> 0) ';
    $params = array($cm->instance, $studentId);
    return $DB->record_exists_sql($sql, $params);
}

function is_teacher_has_unread_messages(int $coursework, int $teacher, int $student) : bool 
{
    global $DB;
    $conditions = array('coursework' => $coursework, 'userto' => $teacher, 
                        'userfrom' => $student,'readed' => 0);
    return $DB->record_exists('coursework_chat', $conditions);
}

function is_teacher_need_to_check_sections(\stdClass $cm, int $student) : bool 
{
    if(count(get_need_to_check_task_sections($cm, $student)))
    {
        return true;
    }
    else 
    {
        return false;
    }
}

function get_need_to_check_task_sections(\stdClass $cm, int $studentId)
{
    global $DB;
    $sql = 'SELECT cts.*, css.timemodified AS tasksubmissiondate 
            FROM {coursework_tasks_sections} AS cts 
            INNER JOIN {coursework_sections_status} AS css
            ON cts.id = css.section 
            WHERE css.coursework = ?
            AND css.student = ? 
            AND css.status = ? 
            ORDER BY listposition';
    $params = array($cm->instance, $studentId, SENT_TO_CHECK);
    return $DB->get_records_sql($sql, $params);
}


