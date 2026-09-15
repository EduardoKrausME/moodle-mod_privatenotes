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
 * Provides teacher-visible metadata without reading note content.
 *
 * @package   mod_privatenotes
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class metadata_manager {
    /**
     * Gets aggregate metadata.
     *
     * @param int $privatenotesid Activity instance id.
     * @return \stdClass
     */
    public static function get_summary($privatenotesid) {
        global $DB;

        $sql = "SELECT COUNT(id) AS notecount, MAX(timemodified) AS lastmodified
                  FROM {privatenotes_content}
                 WHERE privatenotesid = :privatenotesid";
        $record = $DB->get_record_sql($sql, ["privatenotesid" => $privatenotesid]);

        return (object)[
            "count" => (int)$record->notecount,
            "lastmodified" => $record->lastmodified ? (int)$record->lastmodified : 0,
        ];
    }

    /**
     * Gets individual metadata without selecting the content column.
     *
     * @param int $privatenotesid Activity instance id.
     * @return array
     */
    public static function get_individual_metadata($privatenotesid) {
        global $DB;

        return $DB->get_records(
            "privatenotes_content",
            ["privatenotesid" => $privatenotesid],
            "timemodified DESC",
            "id, userid, timecreated, timemodified"
        );
    }
}
