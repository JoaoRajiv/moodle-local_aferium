<?php
// This file is part of the Aferium plugin for Moodle.
//
// It is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
// License as published by the Free Software Foundation, either version 3 of the License, or (at your option)
// any later version. It is distributed WITHOUT ANY WARRANTY. See <http://www.gnu.org/licenses/>.

namespace local_aferium;

/**
 * The connection handshake: a teacher authorizes in Moodle, Aferium receives a one-time code in the browser
 * and its server exchanges the code for a web service token. The token never travels through the browser.
 *
 * @package   local_aferium
 * @copyright 2026 Aferium
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class handshake {
    /** Shortname of the service declared in db/services.php. */
    const SERVICE = 'local_aferium';
    /** Shortname of the role that allows REST for connected teachers. */
    const ROLE = 'aferiumws';
    /** Seconds a code stays valid. */
    const CODE_TTL = 300;

    /** The Aferium address codes are sent to. Only the administrator can change it. */
    public static function aferium_url(): string {
        return rtrim(get_config('local_aferium', 'aferiumurl') ?: 'https://aferium.com.br', '/');
    }

    /** Only someone who can see quiz reports in at least one course (a teacher) may connect. */
    public static function can_connect(int $userid): bool {
        return (bool) get_user_capability_course('mod/quiz:viewreports', $userid, true, '', '', 1);
    }

    /** True when the site can answer Aferium: web services, REST and the service are all enabled. */
    public static function is_ready(): bool {
        global $CFG, $DB;
        return !empty($CFG->enablewebservices)
            && in_array('rest', explode(',', $CFG->webserviceprotocols ?? ''))
            && $DB->record_exists('external_services', ['shortname' => self::SERVICE, 'enabled' => 1]);
    }

    /** Creates the role on first use and returns its id. */
    public static function ensure_role(): int {
        global $DB;
        if ($roleid = $DB->get_field('role', 'id', ['shortname' => self::ROLE])) {
            return $roleid;
        }
        $roleid = create_role('Aferium', self::ROLE, get_string('roledescription', 'local_aferium'));
        set_role_contextlevels($roleid, [CONTEXT_SYSTEM]);
        assign_capability('webservice/rest:use', CAP_ALLOW, $roleid, \context_system::instance()->id, true);
        return $roleid;
    }

    /**
     * Issues the code the browser carries back to Aferium. Only its hash is stored.
     *
     * @param int $userid the teacher who authorized
     * @param string $challenge base64url SHA-256 of a secret only the Aferium server knows (PKCE)
     */
    public static function create_code(int $userid, string $challenge): string {
        $code = bin2hex(random_bytes(32));
        \cache::make('local_aferium', 'codes')->set(hash('sha256', $code), [
            'userid' => $userid,
            'challenge' => $challenge,
            'expires' => time() + self::CODE_TTL,
        ]);
        return $code;
    }

    /**
     * Exchanges a code for a token. A code works once, even when the exchange fails.
     *
     * @return string|null the token, or null when the code, the verifier or the user is not acceptable
     */
    public static function redeem_code(string $code, string $verifier): ?string {
        global $DB;

        $cache = \cache::make('local_aferium', 'codes');
        $key = hash('sha256', $code);
        $lock = \core\lock\lock_config::get_lock_factory('local_aferium_codes')->get_lock($key, 5);
        if (!$lock) {
            return null;
        }
        try {
            $entry = $cache->get($key);
            $cache->delete($key);
        } finally {
            $lock->release();
        }
        if (!$entry || $entry['expires'] < time()) {
            return null;
        }
        $expected = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
        if (!hash_equals($entry['challenge'], $expected)) {
            return null;
        }
        $user = $DB->get_record('user', ['id' => $entry['userid'], 'deleted' => 0, 'suspended' => 0]);
        $service = $DB->get_record('external_services', ['shortname' => self::SERVICE, 'enabled' => 1]);
        if (!$user || !$service || !self::can_connect($user->id)) {
            return null;
        }

        $system = \context_system::instance();
        role_assign(self::ensure_role(), $user->id, $system->id);
        // Connecting again replaces the previous token instead of piling them up.
        $DB->delete_records('external_tokens', ['userid' => $user->id, 'externalserviceid' => $service->id]);
        return \core_external\util::generate_token(EXTERNAL_TOKEN_PERMANENT, $service, $user->id, $system, 0, '', 'Aferium');
    }

    /**
     * Deletes a token of the Aferium service; called when the teacher disconnects in Aferium.
     * Knowing the token is the proof: whoever holds it could use it anyway. Tokens of other services are never touched.
     */
    public static function revoke_token(string $token): void {
        global $DB;

        $serviceid = $DB->get_field('external_services', 'id', ['shortname' => self::SERVICE]);
        $record = $serviceid ? $DB->get_record('external_tokens', ['token' => $token, 'externalserviceid' => $serviceid]) : null;
        if (!$record) {
            return;
        }
        $DB->delete_records('external_tokens', ['id' => $record->id]);
        if ($roleid = $DB->get_field('role', 'id', ['shortname' => self::ROLE])) {
            role_unassign($roleid, $record->userid, \context_system::instance()->id);
        }
    }
}
