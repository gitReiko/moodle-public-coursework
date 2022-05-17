<?php 

/**
 * coursework restore task that provides all the settings and steps to perform one
 * complete restore of the activity
 */

require_once($CFG->dirroot . '/mod/coursework/backup/moodle2/restore_coursework_stepslib.php');

class restore_coursework_activity_task extends restore_activity_task {

    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() 
    {
        // No particular settings for this activity
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() 
    {
        // coursework only has one structure step
        $this->add_step(new restore_coursework_activity_structure_step('coursework_structure', 'coursework.xml'));
    }

    /**
     * Define the contents in the activity that must be
     * processed by the link decoder
     */
    static public function define_decode_contents() 
    {
        $contents = array();

        $contents[] = new restore_decode_content('coursework', array('intro'), 'coursework');
        $contents[] = new restore_decode_content('coursework_chat', array('content'), 'coursework_chat');
        $contents[] = new restore_decode_content('coursework_tasks', array('name', 'description'), 'coursework_tasks');
        $contents[] = new restore_decode_content('coursework_tasks_sections', array('name', 'description'), 'coursework_tasks_sections');
        $contents[] = new restore_decode_content('coursework_themes', array('content'), 'coursework_themes');
        $contents[] = new restore_decode_content('coursework_themes_collections', array('name', 'description'), 'coursework_themes_collections');

        return $contents;
    }

    /**
     * Define the decoding rules for links belonging
     * to the activity to be executed by the link decoder
     */
    static public function define_decode_rules() 
    {
        $rules = array();

        // Default links
        $rules[] = new restore_decode_rule('COURSEWORKVIEWBYID', '/mod/coursework/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('COURSEWORKINDEX', '/mod/coursework/index.php?id=$1', 'course');

        // Config pages
        $rules[] = new restore_decode_rule('CONFIGLISTID', '/mod/coursework/pages/config/list.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('APPOINTLEADERID', '/mod/coursework/pages/config/appoint_leaders.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('DISTRIBUTETOLEADERSID', '/mod/coursework/pages/config/distribute_to_leaders.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('SETDEFAULTTASKTEMPLATEID', '/mod/coursework/pages/config/set_default_task_template.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('SETSUGGESTEDTHEMESID', '/mod/coursework/pages/config/set_suggested_themes.php?id=$1', 'course_module');

        // Support pages
        $rules[] = new restore_decode_rule('SUPPORTLISTID', '/mod/coursework/pages/support/list.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('DELETESTUDENTCOURSEWORKID', '/mod/coursework/pages/support/delete_student_coursework.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('LEADERREPLACEMENTID', '/mod/coursework/pages/support/leader_replacement.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('RETURNTOTHEMESELECTIONID', '/mod/coursework/pages/support/return_to_theme_selection.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('RETURNWORKFORREWORKID', '/mod/coursework/pages/support/return_work_for_rework.php?id=$1', 'course_module');

        // Other pages
        $rules[] = new restore_decode_rule('MANAGEOLDFILESAREAID', '/mod/coursework/pages/manage_old_files_area.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('QUOTAOVERVIEWID', '/mod/coursework/pages/quota_overview.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('TASKSTEMPLATESMANAGEMENTID', '/mod/coursework/pages/tasks_templates_management.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('THEMESCOLLECTIONSMANAGEMENTID', '/mod/coursework/pages/themes_collections_management.php?id=$1', 'course_module');

        return $rules;
    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * coursework logs. It must return one array
     * of {@link restore_log_rule} objects
     */
    static public function define_restore_log_rules() 
    {
        $rules = array();

        return $rules;
    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * course logs. It must return one array
     * of {@link restore_log_rule} objects
     *
     * Note this rules are applied when restoring course logs
     * by the restore final task, but are defined here at
     * activity level. All them are rules not linked to any module instance (cmid = 0)
     */
    static public function define_restore_log_rules_for_course() 
    {
        $rules = array();

        return $rules;
    }

}
