<?php
// This file is part of the Aferium plugin for Moodle.
//
// It is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
// License as published by the Free Software Foundation, either version 3 of the License, or (at your option)
// any later version. It is distributed WITHOUT ANY WARRANTY. See <http://www.gnu.org/licenses/>.

/**
 * The service Aferium uses: core read functions plus the plugin's own: build a quiz, read the marked options.
 *
 * @package   local_aferium
 * @copyright 2026 Aferium
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_aferium_get_attempt_choices' => [
        'classname' => 'local_aferium\\external\\get_attempt_choices',
        'description' => 'Returns which options a student marked in each question of a quiz attempt.',
        'type' => 'read',
        'capabilities' => 'mod/quiz:viewreports',
    ],
    'local_aferium_create_quiz' => [
        'classname' => 'local_aferium\\external\\create_quiz',
        'description' => 'Creates a quiz in a course from an Aferium assessment (questions in Moodle XML).',
        'type' => 'write',
        'capabilities' => 'mod/quiz:addinstance, moodle/course:manageactivities, moodle/question:add',
    ],
];

$services = [
    // Not plain "Aferium": service names are unique and sites that followed the manual guide already have that one.
    'Aferium (plugin)' => [
        'functions' => [
            'core_webservice_get_site_info',
            'core_enrol_get_users_courses',
            'core_enrol_get_enrolled_users',
            'mod_quiz_get_quizzes_by_courses',
            'mod_quiz_get_user_attempts',
            'mod_quiz_get_attempt_review',
            'local_aferium_create_quiz',
            'local_aferium_get_attempt_choices',
        ],
        'restrictedusers' => 0,
        'enabled' => 1,
        'shortname' => 'local_aferium',
        'downloadfiles' => 0,
        'uploadfiles' => 0,
    ],
];
