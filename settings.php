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

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_aferium', get_string('pluginname', 'local_aferium'));
    $settings->add(new admin_setting_configtext(
        'local_aferium/aferiumurl',
        get_string('aferiumurl', 'local_aferium'),
        get_string('aferiumurl_desc', 'local_aferium'),
        'https://aferium.com.br',
        PARAM_URL
    ));
    $ADMIN->add('localplugins', $settings);
}
