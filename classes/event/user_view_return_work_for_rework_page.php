<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
 
/**
 * The user_view_return_work_for_rework_page event.
 *
 * @package    mod_coursework
 * @copyright  2021 Denis Makouski (Reiko)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_coursework\event;
defined('MOODLE_INTERNAL') || die();
/**
 * The user_view_return_work_for_rework_page event class.
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      User viewed back to work state page.
 * }
 *
 * @since     Moodle 20190725
 * @copyright 2021 Denis Makouski (Reiko)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

class user_view_return_work_for_rework_page extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'r'; // c(reate), r(ead), u(pdate), d(elete)
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }
 
    public static function get_name() {
    
        return get_string('user_view_return_work_for_rework_page', 'coursework');
    }
 
    public function get_description() 
    {
        return "The user with id '$this->userid' views back to work state page.";
    }
 
    public function get_url() {
        return new \moodle_url('/mod/coursework/view.php', array('id' => $this->contextinstanceid));
    }
 
    public function get_legacy_logdata() {
        // Override if you are migrating an add_to_log() call.
        return array($this->courseid, 'PLUGINNAME', 'LOGACTION',
            '...........',
            $this->objectid, $this->contextinstanceid);
    }
 
    public static function get_legacy_eventname() {
        // Override ONLY if you are migrating events_trigger() call.
        return 'MYPLUGIN_OLD_EVENT_NAME';
    }
 
    protected function get_legacy_eventdata() {
        // Override if you migrating events_trigger() call.
        $data = new \stdClass();
        $data->id = $this->objectid;
        $data->userid = $this->relateduserid;
        return $data;
    }

}
