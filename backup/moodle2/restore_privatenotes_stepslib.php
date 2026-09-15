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
 * Restore structure for mod_privatenotes.
 *
 * @package   mod_privatenotes
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Restores the activity configuration only.
 */
class restore_privatenotes_activity_structure_step extends restore_activity_structure_step {
    /**
     * Defines restore paths.
     *
     * @return array
     */
    protected function define_structure() {
        $paths = [new restore_path_element("privatenotes", "/activity/privatenotes")];
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Restores the activity instance.
     *
     * @param array $data Backup data.
     */
    protected function process_privatenotes($data) {
        global $DB;

        $data = (object)$data;
        $data->course = $this->get_courseid();
        $newitemid = $DB->insert_record("privatenotes", $data);
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Restores standard intro files.
     */
    protected function after_execute() {
        $this->add_related_files("mod_privatenotes", "intro", null);
    }
}
