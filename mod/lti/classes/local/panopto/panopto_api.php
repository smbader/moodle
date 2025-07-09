<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die;

require_once(__DIR__ . '/panopto_soap.php');

/**
 * Panopto API layer (so you don't need to worry about if it's SOAP or REST).
 *
 * @package mod_lti
 * @copyright 2020-2025 Alex Stacy, Jonathan Champ
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class panopto_api {
    /**
     * Array of initialized SOAP API objects.
     *
     * @var panopto_soap[]
     */
    private $soap = [];

    /**
     * The Panopto domain name.
     *
     * @var string
     */
    private $server_name;

    /**
     * Constructor
     */
    public function __construct($server_name) {
        $this->server_name = $server_name;
    }

    /**
     * Initialize SOAP API object.
     *
     * @return panopto_soap
     */
    private function init_soap_auth() {
        if (empty($this->soap['auth'])) {
            $wsdl_url = 'https://' . $this->server_name . '/Panopto/PublicAPI/4.2/Auth.svc?singleWsdl';
            $this->soap['auth'] = new panopto_soap($wsdl_url);
        }

        return $this->soap['auth'];
    }

    /**
     * Initialize SOAP API object.
     *
     * @return panopto_soap
     */
    private function init_soap_user_management() {
        if (empty($this->soap['user_management'])) {
            $wsdl_url = 'https://' . $this->server_name . '/Panopto/PublicAPI/4.6/UserManagement.svc?singleWsdl';
            $this->soap['user_management'] = new panopto_soap($wsdl_url);
        }

        return $this->soap['user_management'];
    }

    /**
     * Initialize SOAP API object.
     *
     * @return panopto_soap
     */
    private function init_soap_session_management() {
        if (empty($this->soap['session_management'])) {
            $wsdl_url = 'https://' . $this->server_name . '/Panopto/PublicAPI/4.6/SessionManagement.svc?singleWsdl';
            $this->soap['session_management'] = new panopto_soap($wsdl_url);
        }

        return $this->soap['session_management'];
    }

    /**
     * Initialize SOAP API object.
     *
     * @return panopto_soap
     */
    private function init_soap_access_management() {
        if (empty($this->soap['access_management'])) {
            $wsdl_url = 'https://' . $this->server_name . '/Panopto/PublicAPI/4.6/AccessManagement.svc?singleWsdl';
            $this->soap['access_management'] = new panopto_soap($wsdl_url);
        }

        return $this->soap['access_management'];
    }

    /**
     * Get instance name.
     *
     * @param string $provider Provider key.
     * @return ?string
     */
    public function get_instance_name($provider) {
        $api_credentials = $this->get_api_credentials();
        $providers = $api_credentials['providers'];

        if (!empty($providers[$provider])) {
            return $providers[$provider]['instance_name'];
        }

        return null;
    }

    /**
     * Gets the user access details of an individual user, such as
     * what the user has creator/viewer access to.
     *
     * @param string $group_id Group GUID.
     * @return array
     */
    public function get_group_access_details($group_id, $auth) {
        $soap = $this->init_soap_access_management();

        $group = new stdClass();
        $group->auth = $auth;
        $group->groupId = $group_id;

        $response = $soap->GetGroupAccessDetails($group);
        return $response->GetGroupAccessDetailsResult;
    }

    /**
     * Gets groups by name.
     *
     * @param string $group_name Group name.
     * @return array
     */
    public function get_groups_by_name($group_name, $auth) {
        $soap = $this->init_soap_user_management();

        $group = new stdClass();
        $group->auth = $auth;
        $group->groupName = $group_name;

        $response = $soap->GetGroupsByName($group);
        return $response->GetGroupsByNameResult->Group ?? [];
    }

    /**
     * Get a Panopto session.
     *
     * @param array $session_ids Array of Session GUIDs.
     * @return array
     */
    public function get_sessions_by_id($session_ids, $auth) {
        if (empty($session_ids)) {
            return [];
        }

        $soap = $this->init_soap_session_management();

        $get_session_list_request = new stdClass();
        $get_session_list_request->auth = $auth;
        $get_session_list_request->sessionIds = new Arrayofguid($session_ids);

        $response = $soap->GetSessionsById($get_session_list_request);
        return $response->GetSessionsByIdResult->Session ?? [];
    }
}
