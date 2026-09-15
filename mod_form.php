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
 * Activity settings form.
 *
 * @package   mod_privatenotes
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_privatenotes\note_manager;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . "/course/moodleform_mod.php");

/**
 * Activity settings form for mod_privatenotes.
 */
class mod_privatenotes_mod_form extends moodleform_mod {
    /**
     * Defines the form.
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement("header", "general", get_string("general", "form"));
        $mform->addElement("text", "name", get_string("privatenotesname", "mod_privatenotes"), ["size" => 64]);
        $mform->setType("name", PARAM_TEXT);
        $mform->addRule("name", null, "required", null, "client");
        $mform->addRule("name", get_string("maximumchars", "", 255), "maxlength", 255, "client");

        $this->standard_intro_elements();

        $options = [
            note_manager::METADATA_NONE => get_string("metadata:none", "mod_privatenotes"),
            note_manager::METADATA_AGGREGATE => get_string("metadata:aggregate", "mod_privatenotes"),
            note_manager::METADATA_INDIVIDUAL => get_string("metadata:individual", "mod_privatenotes"),
        ];
        $mform->addElement("select", "metadatavisibility", get_string("metadatavisibility", "mod_privatenotes"), $options);
        $mform->setDefault("metadatavisibility", note_manager::METADATA_NONE);
        $mform->addHelpButton("metadatavisibility", "metadatavisibility", "mod_privatenotes");

        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }
}
