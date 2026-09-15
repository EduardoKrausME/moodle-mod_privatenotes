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

namespace mod_privatenotes\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy API implementation.
 *
 * @package   mod_privatenotes
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
        \core_privacy\local\metadata\provider,
        \core_privacy\local\request\plugin\provider,
        \core_privacy\local\request\core_userlist_provider {

    /**
     * Describes stored personal data.
     *
     * @param collection $collection Metadata collection.
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table("privatenotes_content", [
            "privatenotesid" => "privacy:metadata:privatenotes_content:privatenotesid",
            "userid" => "privacy:metadata:privatenotes_content:userid",
            "content" => "privacy:metadata:privatenotes_content:content",
            "contentformat" => "privacy:metadata:privatenotes_content:contentformat",
            "timecreated" => "privacy:metadata:privatenotes_content:timecreated",
            "timemodified" => "privacy:metadata:privatenotes_content:timemodified",
        ], "privacy:metadata:privatenotes_content");
        return $collection;
    }

    /**
     * Returns contexts containing data for a user.
     *
     * @param int $userid User id.
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $sql = "SELECT ctx.id
                  FROM {context} ctx
                  JOIN {course_modules} cm
                    ON cm.id = ctx.instanceid
                   AND ctx.contextlevel = :contextlevel
                  JOIN {modules} m
                    ON m.id = cm.module
                   AND m.name = :modname
                  JOIN {privatenotes} pn
                    ON pn.id = cm.instance
                  JOIN {privatenotes_content} pnc
                    ON pnc.privatenotesid = pn.id
                 WHERE pnc.userid = :userid";

        $params = [
            "contextlevel" => CONTEXT_MODULE,
            "modname" => "privatenotes",
            "userid" => $userid,
        ];

        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, $params);
        return $contextlist;
    }

    /**
     * Returns users whose data exists in the supplied context.
     *
     * @param userlist $userlist User list.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();
        if (!($context instanceof \context_module)) {
            return;
        }

        $sql = "SELECT pnc.userid
                  FROM {privatenotes_content} pnc
                  JOIN {course_modules} cm ON cm.instance = pnc.privatenotesid
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                 WHERE cm.id = :cmid";
        $userlist->add_from_sql("userid", $sql, [
            "modname" => "privatenotes",
            "cmid" => $context->instanceid,
        ]);
    }

    /**
     * Exports a user's own private note data.
     *
     * @param approved_contextlist $contextlist Approved contexts.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            $instance = self::get_instance_from_context($context);
            if (!$instance) {
                continue;
            }

            $note = $DB->get_record("privatenotes_content", [
                "privatenotesid" => $instance->id,
                "userid" => $userid,
            ]);
            if (!$note) {
                continue;
            }

            $data = (object)[
                "activity" => format_string($instance->name),
                "content" => $note->content,
                "contentformat" => $note->contentformat,
                "timecreated" => transform::datetime($note->timecreated),
                "timemodified" => transform::datetime($note->timemodified),
            ];
            writer::with_context($context)->export_data([], $data);
        }
    }

    /**
     * Deletes all private note data in a module context.
     *
     * @param \context $context Context.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        $instance = self::get_instance_from_context($context);
        if ($instance) {
            $DB->delete_records("privatenotes_content", ["privatenotesid" => $instance->id]);
        }
    }

    /**
     * Deletes one user's private notes from approved contexts.
     *
     * @param approved_contextlist $contextlist Approved contexts.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            $instance = self::get_instance_from_context($context);
            if ($instance) {
                $DB->delete_records("privatenotes_content", [
                    "privatenotesid" => $instance->id,
                    "userid" => $userid,
                ]);
            }
        }
    }

    /**
     * Deletes selected users' data in one context.
     *
     * @param approved_userlist $userlist Approved users.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $instance = self::get_instance_from_context($userlist->get_context());
        if (!$instance) {
            return;
        }

        $userids = $userlist->get_userids();
        if (empty($userids)) {
            return;
        }

        list($insql, $params) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, "userid");
        $params["privatenotesid"] = $instance->id;
        $DB->delete_records_select(
            "privatenotes_content",
            "privatenotesid = :privatenotesid AND userid {$insql}",
            $params
        );
    }

    /**
     * Resolves an activity instance from a module context.
     *
     * @param \context $context Context.
     * @return \stdClass|false
     */
    private static function get_instance_from_context(\context $context) {
        global $DB;

        if (!($context instanceof \context_module)) {
            return false;
        }

        $cm = get_coursemodule_from_id("privatenotes", $context->instanceid, 0, false, IGNORE_MISSING);
        if (!$cm) {
            return false;
        }

        return $DB->get_record("privatenotes", ["id" => $cm->instance]);
    }
}
