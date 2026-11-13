<?php
// This file is part of the Aferium plugin for Moodle.
//
// It is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
// License as published by the Free Software Foundation, either version 3 of the License, or (at your option)
// any later version. It is distributed WITHOUT ANY WARRANTY. See <http://www.gnu.org/licenses/>.

/**
 * Server-to-server endpoint: Aferium exchanges the one-time code for the teacher's token, and gives the token
 * back (revoke) when the teacher disconnects.
 * There is no session here; the code plus the PKCE verifier are the credentials.
 *
 * @package   local_aferium
 * @copyright 2026 Aferium
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);
define('NO_MOODLE_COOKIES', true);

require('../../config.php');

$code = optional_param('code', '', PARAM_ALPHANUMEXT);
$verifier = optional_param('verifier', '', PARAM_ALPHANUMEXT);
$revoke = optional_param('revoke', '', PARAM_ALPHANUM);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// An empty request is how Aferium checks that the plugin is installed before sending the teacher here.
$answer = ['plugin' => 'local_aferium', 'error' => 'invalid_request'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $revoke !== '') {
    // The answer is the same whether or not the token existed.
    \local_aferium\handshake::revoke_token($revoke);
    $answer = ['plugin' => 'local_aferium', 'revoked' => true];
} else if ($_SERVER['REQUEST_METHOD'] === 'POST' && $code !== '' && $verifier !== '') {
    $token = \local_aferium\handshake::redeem_code($code, $verifier);
    $answer = $token ? ['plugin' => 'local_aferium', 'token' => $token] : ['plugin' => 'local_aferium', 'error' => 'invalid_code'];
}
echo json_encode($answer);
