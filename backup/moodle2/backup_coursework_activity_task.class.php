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
    static public function encode_content_links($content) {
        return $content;
    }
}
