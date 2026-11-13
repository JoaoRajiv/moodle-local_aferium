<?php
// This file is part of the Aferium plugin for Moodle.
//
// It is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
// License as published by the Free Software Foundation, either version 3 of the License, or (at your option)
// any later version. It is distributed WITHOUT ANY WARRANTY. See <http://www.gnu.org/licenses/>.

/**
 * The page a teacher lands on from Aferium to authorize the connection.
 *
 * @package   local_aferium
 * @copyright 2026 Aferium
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

use local_aferium\handshake;

$state = required_param('state', PARAM_ALPHANUMEXT);
$challenge = required_param('challenge', PARAM_ALPHANUMEXT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

$params = ['state' => $state, 'challenge' => $challenge];
$PAGE->set_url('/local/aferium/connect.php', $params);
$PAGE->set_context(context_system::instance());
require_login(null, false);

$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('connecttitle', 'local_aferium'));
$PAGE->set_heading(get_string('connecttitle', 'local_aferium'));

$aferium = handshake::aferium_url();
$back = new moodle_url($aferium . '/avaliacoes/moodle');

// A SHA-256 challenge in base64url is always 43 characters.
$problem = null;
if (strlen($challenge) !== 43 || strlen($state) < 20 || strlen($state) > 128) {
    $problem = 'invalidrequest';
} else if (!handshake::is_ready()) {
    $problem = 'notready';
} else if (isguestuser() || !handshake::can_connect($USER->id)) {
    $problem = 'notteacher';
}

if (!$problem && $confirm && confirm_sesskey()) {
    $back->params(['code' => handshake::create_code($USER->id, $challenge), 'state' => $state]);
    redirect($back);
}

echo $OUTPUT->header();
if ($problem) {
    echo $OUTPUT->notification(get_string('problem_' . $problem, 'local_aferium'), 'error');
    echo $OUTPUT->single_button($back, get_string('backtoaferium', 'local_aferium'), 'get');
} else {
    $host = parse_url($aferium, PHP_URL_HOST);
    $message = get_string('connectintro', 'local_aferium', (object) ['name' => s(fullname($USER)), 'host' => s($host)])
        . html_writer::alist([
            get_string('reads_courses', 'local_aferium'),
            get_string('reads_students', 'local_aferium'),
            get_string('reads_attempts', 'local_aferium'),
            get_string('writes_quiz', 'local_aferium'),
        ])
        . html_writer::tag('p', get_string('connectreadonly', 'local_aferium'));
    $continue = new single_button(
        new moodle_url('/local/aferium/connect.php', $params + ['confirm' => 1]),
        get_string('authorize', 'local_aferium'),
        'post',
        single_button::BUTTON_PRIMARY
    );
    echo $OUTPUT->confirm($message, $continue, $back);
}
echo $OUTPUT->footer();
