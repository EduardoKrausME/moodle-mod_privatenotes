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
 * Teacher-facing metadata report. This page never queries note content.
 *
 * @package   mod_privatenotes
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . "/../../config.php");

$id = required_param("id", PARAM_INT);
$cm = get_coursemodule_from_id("privatenotes", $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$privatenotes = $DB->get_record("privatenotes", ["id" => $cm->instance], "*", MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability("mod/privatenotes:viewmetadata", $context);

if ((int)$privatenotes->metadatavisibility !== \mod_privatenotes\note_manager::METADATA_INDIVIDUAL) {
    throw new moodle_exception("individualmetadatadisabled", "mod_privatenotes");
}

$PAGE->set_url("/mod/privatenotes/metadata.php", ["id" => $cm->id]);
$PAGE->set_title(get_string("metadatareport", "mod_privatenotes"));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$rows = [];
foreach (\mod_privatenotes\metadata_manager::get_individual_metadata($privatenotes->id) as $record) {
    $user = \core_user::get_user($record->userid, "*", MUST_EXIST);
    $rows[] = [
        "fullname" => fullname($user),
        "timecreated" => userdate($record->timecreated),
        "timemodified" => userdate($record->timemodified),
    ];
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string("metadatareport", "mod_privatenotes"));
echo $OUTPUT->notification(get_string("metadatanocontent", "mod_privatenotes"), \core\output\notification::NOTIFY_INFO);
echo $OUTPUT->render_from_template("mod_privatenotes/metadata_table", [
    "hasrows" => !empty($rows),
    "rows" => $rows,
]);
echo $OUTPUT->footer();
