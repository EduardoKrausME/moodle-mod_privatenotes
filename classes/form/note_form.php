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

namespace mod_privatenotes\form;

/**
 * Private note editor form.
 *
 * @package   mod_privatenotes
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class note_form extends \moodleform {
    /**
     * Defines the note editor.
     */
    public function definition() {
        $mform = $this->_form;
        $editoroptions = $this->_customdata["editoroptions"];

        $mform->addElement(
            "editor",
            "content_editor",
            get_string("yournotes", "mod_privatenotes"),
            ["rows" => 18],
            $editoroptions
        );
        $mform->setType("content_editor", PARAM_RAW);

        $buttonarray = [];
        $buttonarray[] = $mform->createElement("submit", "save", get_string("savenote", "mod_privatenotes"));
        $buttonarray[] = $mform->createElement("submit", "delete", get_string("deletenote", "mod_privatenotes"));
        $mform->addGroup($buttonarray, "buttonar", "", [" "], false);
    }
}
