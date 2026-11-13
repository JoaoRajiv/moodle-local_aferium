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

$string['aferiumurl'] = 'Aferium address';
$string['aferiumurl_desc'] = 'Where teachers are sent back after authorizing. Change it only if Aferium support asks you to.';
$string['authorize'] = 'Authorize';
$string['backtoaferium'] = 'Back to Aferium';
$string['cachedef_codes'] = 'One-time connection codes';
$string['connectintro'] = '<p>{$a->name}, <strong>{$a->host}</strong> is asking to connect to this Moodle with your account. Aferium will be able to, only in your courses:</p>';
$string['connectreadonly'] = 'Aferium only creates a quiz when you ask for it in Aferium. It changes nothing else in Moodle and never receives your password.';
$string['connecttitle'] = 'Connect to Aferium';
$string['importfailed'] = 'Moodle could not import the questions sent by Aferium.';
$string['pluginname'] = 'Aferium';
$string['privacy:metadata:aferium'] = 'When a teacher connects, Aferium reads data from that teacher\'s courses through Moodle web services.';
$string['privacy:metadata:aferium:attempts'] = 'Quiz attempts: state, grade, the result of each question and which options the student marked.';
$string['privacy:metadata:aferium:email'] = 'The email address of enrolled students, when the teacher can see it.';
$string['privacy:metadata:aferium:fullname'] = 'The full name of enrolled students.';
$string['problem_invalidrequest'] = 'This connection link is not valid. Go back to Aferium and start again.';
$string['problem_notready'] = 'Web services are turned off on this Moodle. Ask the administrator to enable web services and the REST protocol.';
$string['problem_notteacher'] = 'Only teachers can connect to Aferium. Your account cannot see quiz reports in any course.';
$string['reads_attempts'] = 'read the quiz attempts and results';
$string['reads_courses'] = 'read your courses and their quizzes';
$string['reads_students'] = 'read the name and email of enrolled students';
$string['roledescription'] = 'Lets a teacher who connected to Aferium use the REST web service. Assigned automatically by the Aferium plugin.';
$string['writes_quiz'] = 'create a quiz with the questions of an assessment, when you ask for it';
