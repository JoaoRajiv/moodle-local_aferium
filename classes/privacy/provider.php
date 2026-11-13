<?php
// This file is part of the Aferium plugin for Moodle.
//
// It is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
// License as published by the Free Software Foundation, either version 3 of the License, or (at your option)
// any later version. It is distributed WITHOUT ANY WARRANTY. See <http://www.gnu.org/licenses/>.

namespace local_aferium\privacy;

use core_privacy\local\metadata\collection;

/**
 * The plugin keeps no personal data itself; it lets a teacher send course data to Aferium.
 *
 * @package   local_aferium
 * @copyright 2026 Aferium
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \core_privacy\local\metadata\provider {
    public static function get_metadata(collection $collection): collection {
        $collection->add_external_location_link('aferium', [
            'fullname' => 'privacy:metadata:aferium:fullname',
            'email' => 'privacy:metadata:aferium:email',
            'attempts' => 'privacy:metadata:aferium:attempts',
        ], 'privacy:metadata:aferium');
        return $collection;
    }
}
