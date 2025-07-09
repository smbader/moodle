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

/**
 * The special restore steps for the Panopto LTI.
 *
 * @copyright 2025 Steve Bader
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package mod_lti
 */
class restore_lti_panopto {
    public static function get_session_information($groupname) {
        global $CFG;

        // DELTA API for interacting with Panopto using SOAP.
        require_once(__DIR__ . '/../../classes/local/panopto/panopto_api.php');
        // Loading cURL helpers.
        require_once($CFG->libdir . '/filelib.php');

        $servernameappkey = self::get_servername_appkey();

        $auth = self::get_auth($servernameappkey['servername'], $servernameappkey['applicationkey']);

        // Create Panopto API Soap.
        $panoptoapi = new panopto_api($servernameappkey['servername']);

        $groups = $panoptoapi->get_groups_by_name($groupname, $auth);

        // It's possible to receive more than one group, I guess?
        foreach ($groups as $group) {
            // Specifically we're looking for the external group created by LTI.
            if ($group->GroupType === 'External' && $group->Name === $groupname) {
                // What does this group have access to?  It should only be one session.
                $response = $panoptoapi->get_group_access_details($group->Id, $auth);

                // Panopto can return multiple sessions attached to the group even though we don't expect that.
                $sessions = $response->SessionsWithViewerAccess->guid ?? [];

                if (!empty($sessions)) {
                    // Loop through all attached sessions, even though we only expect one.
                    $session = $panoptoapi->get_sessions_by_id($sessions, $auth);

                    if (count($session) > 0) {
                        return $session[0];
                    }
                }
            }
        }
        return false;
    }

    private static function get_auth($servername, $applicationkey) {
        global $CFG;

        // Load Panopto helper functions for creating userkeys and auth codes.
        require_once($CFG->dirroot . '/blocks/panopto/lib/block_panopto_lib.php');

        $moodleusername = "smbader@ncsu.edu";
        $providerinstancename = get_config('block_panopto', 'instance_name');

        $apiuseruserkey = $providerinstancename . '\\' . $moodleusername;
        $apiuserauthcode = panopto_generate_auth_code($apiuseruserkey . '@' . $servername, $applicationkey);

        // Create Auth Object for SOAP connection.
        return new AuthenticationInfo($apiuserauthcode, null, $apiuseruserkey);
    }

    private static function get_servername_appkey() {
        // This is the fun way Panopto gets the configured server (since there can be multiple).
        $numservers = get_config('block_panopto', 'server_number');
        $numservers = isset($numservers) ? $numservers : 0;

        // Increment numservers by 1 to take into account starting at 0.
        ++$numservers;

        for ($serverwalker = 1; $serverwalker <= $numservers; $serverwalker++) {
            // Generate strings corresponding to potential servernames in the config.
            $servername = get_config('block_panopto', 'server_name' . $serverwalker);
            $applicationkey = get_config('block_panopto', 'application_key' . $serverwalker);

            if ($servername && $applicationkey) {
                return ['servername' => $servername, 'applicationkey' => $applicationkey];
            }
        }
        return false;
    }

    public static function get_notification_text($sessioninfo) {
        return '
            <div class="alert alert-danger alert-block">
                <p><strong>Instructors</strong>: This Panopto link is no longer
                working. This can occur when Panopto links are copied from a previous academic year or from a Projects
                or Outreach course.</p>
                <p><strong>Action Required</strong>: Please re-link this video using these step-by-step instructions:
                    <a href="https://ncsu.service-now.com/delta?id=kb_article_ml&amp;sysparm_article=KB0023113"
                       target="_blank" rel="noopener">Re-Linking Panopto Videos in a Copied Course</a>.
                </p>
              <br>
              <strong>Panopto Video Details</strong>:
              <img class="img-fluid" style="float: right;"
                   role="presentation"
                   src="' . $sessioninfo->ThumbUrl . '"
                   alt=""
                   width="200">
              <ul>
                <li>Impacted Video: ' . htmlspecialchars($sessioninfo->Name) . '</li>
                <li>Folder: ' . htmlspecialchars($sessioninfo->FolderName) . '</li>
              </ul>
              <br>
              <br>
            </div>';
    }
}
