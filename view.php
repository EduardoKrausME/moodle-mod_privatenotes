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
 * Main activity page.
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
require_capability("mod/privatenotes:view", $context);

$PAGE->set_url("/mod/privatenotes/view.php", ["id" => $cm->id]);
$PAGE->set_title(format_string($privatenotes->name, true, ["context" => $context]));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$event = \mod_privatenotes\event\course_module_viewed::create([
    "objectid" => $privatenotes->id,
    "context" => $context,
]);
$event->add_record_snapshot("course", $course);
$event->add_record_snapshot("course_modules", $cm);
$event->add_record_snapshot("privatenotes", $privatenotes);
$event->trigger();

$form = null;
if (has_capability("mod/privatenotes:write", $context)) {
    $editoroptions = [
        "maxfiles" => 0,
        "maxbytes" => 0,
        "trusttext" => false,
        "context" => $context,
    ];
    $form = new \mod_privatenotes\form\note_form(null, [
        "editoroptions" => $editoroptions,
    ]);

    $existing = \mod_privatenotes\note_manager::get_current_user_note($privatenotes->id);
    $formdata = new stdClass();
    $formdata->content_editor = [
        "text" => $existing ? $existing->content : "",
        "format" => $existing ? $existing->contentformat : FORMAT_HTML,
    ];
    $form->set_data($formdata);

    if ($data = $form->get_data()) {
        if (!empty($data->delete)) {
            \mod_privatenotes\note_manager::delete_current_user_note($privatenotes->id);
            redirect(
                new moodle_url("/mod/privatenotes/view.php", ["id" => $cm->id]),
                get_string("notedeleted", "mod_privatenotes"),
                null,
                \core\output\notification::NOTIFY_SUCCESS
            );
        }

        $editor = $data->content_editor;
        \mod_privatenotes\note_manager::save_current_user_note(
            $privatenotes->id,
            $editor["text"],
            $editor["format"]
        );
        redirect(
            new moodle_url("/mod/privatenotes/view.php", ["id" => $cm->id]),
            get_string("notesaved", "mod_privatenotes"),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($privatenotes->name, true, ["context" => $context]));

if (!empty($privatenotes->intro)) {
    echo $OUTPUT->box(format_module_intro("privatenotes", $privatenotes, $cm->id), "generalbox mod_introbox", "privatenotesintro");
}

if ($form) {
    echo $OUTPUT->notification(get_string("privacybanner", "mod_privatenotes"), \core\output\notification::NOTIFY_INFO);
    $form->display();
}

if (has_capability("mod/privatenotes:viewmetadata", $context)
        && (int)$privatenotes->metadatavisibility !== \mod_privatenotes\note_manager::METADATA_NONE) {
    $summary = \mod_privatenotes\metadata_manager::get_summary($privatenotes->id);
    $lastmodified = $summary->lastmodified ? userdate($summary->lastmodified) : get_string("never");
    $templatedata = [
        "counttext" => get_string("notecount", "mod_privatenotes", $summary->count),
        "lastmodifiedtext" => get_string("lastnoteupdate", "mod_privatenotes", $lastmodified),
        "showindividual" => (int)$privatenotes->metadatavisibility === \mod_privatenotes\note_manager::METADATA_INDIVIDUAL,
        "reporturl" => (new moodle_url("/mod/privatenotes/metadata.php", ["id" => $cm->id]))->out(false),
    ];
    echo $OUTPUT->render_from_template("mod_privatenotes/metadata_summary", $templatedata);
}

echo $OUTPUT->footer();
