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
 * Core callbacks for mod_privatenotes.
 *
 * @package   mod_privatenotes
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Returns supported Moodle features.
 *
 * @param string $feature Feature constant.
 * @return mixed
 */
function privatenotes_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
        case FEATURE_SHOW_DESCRIPTION:
        case FEATURE_COMPLETION_TRACKS_VIEWS:
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_GROUPS:
        case FEATURE_GROUPINGS:
            return false;

        case FEATURE_MOD_PURPOSE:
            return MOD_PURPOSE_CONTENT;

        default:
            return null;
    }
}

/**
 * Adds a new activity instance.
 *
 * @param stdClass $data Form data.
 * @param mod_privatenotes_mod_form|null $mform Form instance.
 * @return int
 */
function privatenotes_add_instance($data, $mform = null) {
    return \mod_privatenotes\instance_manager::create($data);
}

/**
 * Updates an activity instance.
 *
 * @param stdClass $data Form data.
 * @param mod_privatenotes_mod_form|null $mform Form instance.
 * @return bool
 */
function privatenotes_update_instance($data, $mform = null) {
    return \mod_privatenotes\instance_manager::update($data);
}

/**
 * Deletes an activity instance and all private notes attached to it.
 *
 * @param int $id Activity instance id.
 * @return bool
 */
function privatenotes_delete_instance($id) {
    return \mod_privatenotes\instance_manager::delete($id);
}

/**
 * Adds the course reset option for this module.
 *
 * @param MoodleQuickForm $mform Reset form.
 */
function privatenotes_reset_course_form_definition($mform) {
    $mform->addElement("header", "privatenotesheader", get_string("modulenameplural", "mod_privatenotes"));
    $mform->addElement("advcheckbox", "reset_privatenotes", get_string("resetnotes", "mod_privatenotes"));
}

/**
 * Defines the default course reset value.
 *
 * @param stdClass $course Course record.
 * @return array
 */
function privatenotes_reset_course_form_defaults($course) {
    return ["reset_privatenotes" => 1];
}

/**
 * Deletes private notes when selected during course reset.
 *
 * @param stdClass $data Reset data.
 * @return array
 */
function privatenotes_reset_userdata($data) {
    global $DB;

    $status = [];
    if (empty($data->reset_privatenotes)) {
        return $status;
    }

    $instances = $DB->get_records("privatenotes", ["course" => $data->courseid], "", "id");
    if ($instances) {
        $instanceids = array_keys($instances);
        list($insql, $params) = $DB->get_in_or_equal($instanceids, SQL_PARAMS_NAMED, "privatenotesid");
        $DB->delete_records_select("privatenotes_content", "privatenotesid {$insql}", $params);
    }

    $status[] = [
        "component" => get_string("modulenameplural", "mod_privatenotes"),
        "item" => get_string("resetnotes", "mod_privatenotes"),
        "error" => false,
    ];

    return $status;
}
