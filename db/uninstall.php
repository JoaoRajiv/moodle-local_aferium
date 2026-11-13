<?php
// This file is part of the Aferium plugin for Moodle.
//
// It is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
// License as published by the Free Software Foundation, either version 3 of the License, or (at your option)
// any later version. It is distributed WITHOUT ANY WARRANTY. See <http://www.gnu.org/licenses/>.

/**
 * @package   local_aferium
 * @copyright 2026 Aferium
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Removes the role (and with it every assignment). Moodle itself removes the service and its tokens.
 * Web services and REST stay as they are: other integrations may depend on them.
 */
function xmldb_local_aferium_uninstall() {
    global $DB;

    if ($roleid = $DB->get_field('role', 'id', ['shortname' => \local_aferium\handshake::ROLE])) {
        delete_role($roleid);
    }
    return true;
}
