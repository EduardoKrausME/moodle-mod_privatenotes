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

namespace mod_privatenotes;

/**
 * Handles private note data for the currently logged-in user.
 *
 * Public methods deliberately do not accept a user id. The owner is always taken from the
 * current Moodle session, preventing a request parameter from selecting another user's note.
 *
 * @package   mod_privatenotes
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class note_manager {
    /** No teacher metadata. */
    const METADATA_NONE = 0;

    /** Aggregate teacher metadata only. */
    const METADATA_AGGREGATE = 1;

    /** Individual teacher metadata without note content. */
    const METADATA_INDIVIDUAL = 2;

    /**
     * Returns the current user's note for one activity.
     *
     * @param int $privatenotesid Activity instance id.
     * @return \stdClass|false
     */
    public static function get_current_user_note($privatenotesid) {
        global $DB, $USER;

        return $DB->get_record("privatenotes_content", [
            "privatenotesid" => $privatenotesid,
            "userid" => $USER->id,
        ]);
    }

    /**
     * Saves the current user's note.
     *
     * @param int $privatenotesid Activity instance id.
     * @param string $content Editor content.
     * @param int $format Text format.
     * @return int Record id.
     */
    public static function save_current_user_note($privatenotesid, $content, $format) {
        global $DB, $USER;

        $now = time();
        $content = clean_text($content, $format);
        $record = self::get_current_user_note($privatenotesid);

        if ($record) {
            $record->content = $content;
            $record->contentformat = $format;
            $record->timemodified = $now;
            $DB->update_record("privatenotes_content", $record);
            return $record->id;
        }

        return $DB->insert_record("privatenotes_content", (object)[
            "privatenotesid" => $privatenotesid,
            "userid" => $USER->id,
            "content" => $content,
            "contentformat" => $format,
            "timecreated" => $now,
            "timemodified" => $now,
        ]);
    }

    /**
     * Deletes the current user's note.
     *
     * @param int $privatenotesid Activity instance id.
     */
    public static function delete_current_user_note($privatenotesid) {
        global $DB, $USER;

        $DB->delete_records("privatenotes_content", [
            "privatenotesid" => $privatenotesid,
            "userid" => $USER->id,
        ]);
    }
}
