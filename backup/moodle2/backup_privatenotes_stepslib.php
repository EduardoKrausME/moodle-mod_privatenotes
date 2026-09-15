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
 * Backup structure for mod_privatenotes.
 *
 * @package   mod_privatenotes
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Defines the activity configuration structure.
 */
class backup_privatenotes_activity_structure_step extends backup_activity_structure_step {
    /**
     * Defines the structure.
     *
     * @return backup_nested_element
     */
    protected function define_structure() {
        $privatenotes = new backup_nested_element("privatenotes", ["id"], [
            "name",
            "intro",
            "introformat",
            "metadatavisibility",
            "timecreated",
            "timemodified",
        ]);

        $privatenotes->set_source_table("privatenotes", ["id" => backup::VAR_ACTIVITYID]);
        $privatenotes->annotate_files("mod_privatenotes", "intro", null);

        return $this->prepare_activity_structure($privatenotes);
    }
}
