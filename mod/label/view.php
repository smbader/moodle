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
 * Label module
 *
 * @package mod_label
 * @copyright  2003 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_once($CFG->dirroot.'/mod/label/lib.php');
require_once($CFG->libdir.'/completionlib.php');

$id = optional_param('id',0,PARAM_INT);    // Course Module ID, or
$l = optional_param('l',0,PARAM_INT);     // Label ID

if ($id) {
    $PAGE->set_url('/mod/label/view.php', array('id' => $id));
    if (! $cm = get_coursemodule_from_id('label', $id, 0, true)) {
        throw new \moodle_exception('invalidcoursemodule');
    }

    if (! $course = $DB->get_record("course", array("id"=>$cm->course))) {
        throw new \moodle_exception('coursemisconf');
    }

    if (! $label = $DB->get_record("label", array("id"=>$cm->instance))) {
        throw new \moodle_exception('invalidcoursemodule');
    }

} else {
    $PAGE->set_url('/mod/label/view.php', array('l' => $l));
    if (! $label = $DB->get_record("label", array("id"=>$l))) {
        throw new \moodle_exception('invalidcoursemodule');
    }
    if (! $course = $DB->get_record("course", array("id"=>$label->course)) ){
        throw new \moodle_exception('coursemisconf');
    }
    if (! $cm = get_coursemodule_from_instance("label", $label->id, $course->id, true)) {
        throw new \moodle_exception('invalidcoursemodule');
    }
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/label:view', $context);

// Completion and trigger events.
label_view($label, $course, $cm, $context);

// Set and print header.
$activityheader = [];
// Need to empty the description, otherwise the label content will be printed out twice.
$activityheader['description'] = '';
$PAGE->set_title($course->shortname.': '.$label->name);
$PAGE->set_heading($course->fullname);
$PAGE->activityheader->set_attrs($activityheader);
echo $OUTPUT->header();

// Set and print content.
$content = file_rewrite_pluginfile_urls($label->intro, 'pluginfile.php', $context->id, 'mod_label', 'intro', null);
$formatcontent = format_text($content, FORMAT_MOODLE, ['noclean' => true, 'overflowdiv' => true, 'context' => $context]);
echo $OUTPUT->box($formatcontent, "generalbox label-content");

echo $OUTPUT->footer();
