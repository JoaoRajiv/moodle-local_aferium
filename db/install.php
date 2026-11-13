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
 * Turns on what the Aferium service needs, so the administrator has nothing to configure by hand:
 * web services, the REST protocol and the role that lets a connected teacher use REST.
 */
function xmldb_local_aferium_install() {
    global $CFG;

    set_config('enablewebservices', 1);
    $protocols = array_filter(explode(',', $CFG->webserviceprotocols ?? ''));
    if (!in_array('rest', $protocols)) {
        $protocols[] = 'rest';
        set_config('webserviceprotocols', implode(',', $protocols));
    }
    \local_aferium\handshake::ensure_role();
    return true;
}
