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
 * Defines forms used by pick.php
 *
 * @package    core_grading
 * @copyright  2011 David Mudrak <david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');

/**
 * Allows to search for a specific shared template
 *
 * @package    core_grading
 * @copyright  2011 David Mudrak <david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class grading_search_template_form extends moodleform {

    /**
     * Filtered search
     */
    public function definition() {
        global $DB, $PAGE, $USER;

        // Get list of courses user has access to as instructor
        $sql = "SELECT c.id, c.fullname
                FROM {course} c
                JOIN {context} ct ON c.id = ct.instanceid
                JOIN {role_assignments} ra ON ra.contextid = ct.id
                JOIN {role} r ON r.id = ra.roleid
                JOIN {user} u ON u.id = ra.userid
                WHERE r.id = 3 AND u.id = :userid";
        $result = $DB->get_records_sql($sql, array('userid' => $USER->id));
        $courses = array('0' => get_string('allcourses', 'core_grading'));
        $activitytypes = array('' => get_string('allactivitytypes', 'core_grading'));
        foreach ($result as $course) {
            $courses[$course->id] = $course->fullname;
            foreach ($this->get_activity_types($course->id) as $activitytype) {
                $activitytypes[$activitytype->areaname] = ucfirst($activitytype->areaname);
            }
        }

        $mform = $this->_form;
        $mform->addElement('header', 'searchheader', get_string('searchtemplate', 'core_grading'));
        $mform->addHelpButton('searchheader', 'searchtemplate', 'core_grading');
        $mform->addElement('text', 'needle', get_string('searchtemplatekeywords', 'core_grading'), array('size' => 30));
        $mform->addElement('select', 'course', get_string('searchtemplatecourse', 'core_grading'), $courses);
        $mform->addElement('select', 'activity_type', get_string('searchtemplateactivitytype', 'core_grading'), $activitytypes);
        $mform->addGroup(array(
            $mform->createElement('checkbox', 'enable_from_date', get_string('searchtemplateenabled', 'core_grading')),
            $mform->createElement('date_time_selector', 'from_date', '', array()),
        ), 'from_date_group', get_string('searchtemplatedatefrom', 'core_grading'), null, false);
        $mform->addElement('date_time_selector', 'to_date', get_string('searchtemplatedateto', 'core_grading'), array());
        $mform->addGroup(array(
            $mform->createElement('submit', 'submitbutton', get_string('search')),
            $mform->createElement('button', 'resetbutton', get_string('searchtemplatereset', 'core_grading'), array('onclick' => 'location.href=\''.$PAGE->url.'\'')),
        ), 'buttonar', '', null, false);
        $mform->disabledIf('from_date_group', 'enable_from_date');
        $mform->setType('needle', PARAM_TEXT);
        $mform->setType('course', PARAM_INT);
        $mform->setType('activity_type', PARAM_TEXT);

    }

    // Get list of activity types for accessible rubrics
    public function get_activity_types($courseid) {
        global $DB;
        $sql = "SELECT DISTINCT ga.areaname
                FROM mdl_grading_definitions gd
                JOIN mdl_grading_areas ga ON gd.areaid = ga.id
                JOIN mdl_context ctx ON ga.contextid = ctx.id
                JOIN mdl_course_modules cm ON ctx.instanceid = cm.id
                JOIN mdl_grade_items gi ON cm.instance = gi.iteminstance
                WHERE ctx.contextlevel = 70
                AND cm.course = :courseid
                AND gd.method = 'rubric'";
       return $DB->get_records_sql($sql, array('courseid' => $courseid));
    }
}
