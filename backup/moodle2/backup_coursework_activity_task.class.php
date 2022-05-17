<?php

require_once($CFG->dirroot . '/mod/coursework/backup/moodle2/backup_coursework_stepslib.php');

/**
 * coursework backup task that provides all the settings and steps to perform one
 * complete backup of the activity
 */

class backup_coursework_activity_task extends backup_activity_task {

    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() 
    {
        
        $this->add_step(new backup_coursework_activity_structure_step('coursework_structure', 'coursework.xml'));
    }

    /**
     * Code the transformations to perform in the activity in
     * order to get transportable (encoded) links
     */
    static public function encode_content_links($content) 
    {
        global $CFG;

        $base = preg_quote($CFG->wwwroot,"/");

        // Default links
        $search="/(".$base."\/mod\/coursework\/index.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@COURSEWORKINDEX*$2@$', $content);

        $search="/(".$base."\/mod\/coursework\/view.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@COURSEWORKVIEWBYID*$2@$', $content);

        // Config pages
        $search="/(".$base."\/mod\/coursework\/pages\/config\/list.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@CONFIGLISTID*$2@$', $content);

        $search="/(".$base."\/mod\/coursework\/pages\/config\/appoint_leaders.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@APPOINTLEADERID*$2@$', $content);

        $search="/(".$base."\/mod\/coursework\/pages\/config\/distribute_to_leaders.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@DISTRIBUTETOLEADERSID*$2@$', $content);

        $search="/(".$base."\/mod\/coursework\/pages\/config\/set_default_task_template.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@SETDEFAULTTASKTEMPLATEID*$2@$', $content);

        $search="/(".$base."\/mod\/coursework\/pages\/config\/set_suggested_themes.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@SETSUGGESTEDTHEMESID*$2@$', $content);

        // Support pages
        $search="/(".$base."\/mod\/coursework\/pages\/support\/list.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@SUPPORTLISTID*$2@$', $content);

        $search="/(".$base."\/mod\/coursework\/pages\/support\/delete_student_coursework.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@DELETESTUDENTCOURSEWORKID*$2@$', $content);

        $search="/(".$base."\/mod\/coursework\/pages\/support\/leader_replacement.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@LEADERREPLACEMENTID*$2@$', $content);

        $search="/(".$base."\/mod\/coursework\/pages\/support\/return_to_theme_selection.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@RETURNTOTHEMESELECTIONID*$2@$', $content);

        $search="/(".$base."\/mod\/coursework\/pages\/support\/return_work_for_rework.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@RETURNWORKFORREWORKID*$2@$', $content);

        // Other pages
        $search="/(".$base."\/mod\/coursework\/pages\/manage_old_files_area.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@MANAGEOLDFILESAREAID*$2@$', $content);

        $search="/(".$base."\/mod\/coursework\/pages\/quota_overview.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@QUOTAOVERVIEWID*$2@$', $content);

        $search="/(".$base."\/mod\/coursework\/pages\/tasks_templates_management.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@TASKSTEMPLATESMANAGEMENTID*$2@$', $content);

        $search="/(".$base."\/mod\/coursework\/pages\/themes_collections_management.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@THEMESCOLLECTIONSMANAGEMENTID*$2@$', $content);

        return $content;
    }
}
