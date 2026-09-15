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
 * Persists activity instance configuration.
 *
 * @package   mod_privatenotes
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class instance_manager {
    /**
     * Creates an instance.
     *
     * @param \stdClass $data Form data.
     * @return int
     */
    public static function create($data) {
        global $DB;

        $now = time();
        $data->timecreated = $now;
        $data->timemodified = $now;
        return $DB->insert_record("privatenotes", $data);
    }

    /**
     * Updates an instance.
     *
     * @param \stdClass $data Form data.
     * @return bool
     */
    public static function update($data) {
        global $DB;

        $data->id = $data->instance;
        $data->timemodified = time();
        return $DB->update_record("privatenotes", $data);
    }

    /**
     * Deletes an instance and its user notes.
     *
     * @param int $id Instance id.
     * @return bool
     */
    public static function delete($id) {
        global $DB;

        if (!$DB->record_exists("privatenotes", ["id" => $id])) {
            return false;
        }

        $transaction = $DB->start_delegated_transaction();
        $DB->delete_records("privatenotes_content", ["privatenotesid" => $id]);
        $DB->delete_records("privatenotes", ["id" => $id]);
        $transaction->allow_commit();
        return true;
    }
}
