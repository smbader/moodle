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

/**
 * File for AuthManagementWsdlClass to communicate with SOAP service
 *
 * @package mod_lti
 * @copyright 2020 Alex Stacy, Jonathan Champ
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * AuthManagementWsdlClass to communicate with SOAP service
 */
class panopto_soap extends SoapClient {
    /**
     * Contains last errors
     *
     * @var array
     */
    private $last_error;

    /**
     * Constructor
     *
     * @param array $wsdl_options WSDL options
     */
    public function __construct(string $wsdl_url, array $wsdl_options = []) {
        // Add default values.
        $wsdl_options += [
            'cache_wsdl' => WSDL_CACHE_NONE,
            'compression' => null,
            'connection_timeout' => null,
            'encoding' => null,
            'exceptions' => true,
            'features' => SOAP_SINGLE_ELEMENT_ARRAYS | SOAP_USE_XSI_ARRAY_TYPE,
            'login' => null,
            'password' => null,
            'soap_version' => null,
            'stream_context' => null,
            'trace' => true,
            'typemap' => null,
            'user_agent' => null,
            'proxy_host' => null,
            'proxy_port' => null,
            'proxy_login' => null,
            'proxy_password' => null,
            'local_cert' => null,
            'passphrase' => null,
            'authentication' => null,
            'ssl_method' => null,
            'classmap' => [
                // Auth.
                'AuthenticationInfo' => 'AuthenticationInfo',
            ],
        ];

        parent::__construct($wsdl_url, $wsdl_options);
    }

    /**
     * Returns the last request content as a DOMDocument or as a formated XML String
     *
     * @param bool $asDomDocument
     * @return DOMDocument|string
     */
    public function getLastRequest($asDomDocument = false) {
        return self::getFormatedXml($this->__getLastRequest(), $asDomDocument);
    }

    /**
     * Returns the last response content as a DOMDocument or as a formated XML String
     *
     * @param bool $asDomDocument
     * @return DOMDocument|string
     */
    public function getLastResponse($asDomDocument = false) {
        return self::getFormatedXml($this->__getLastResponse(), $asDomDocument);
    }

    /**
     * Returns the last request headers used by the SoapClient as the original value or an array
     *
     * @param bool $asArray allows to get the headers in an associative array
     * @return string|array
     */
    public function getLastRequestHeaders($asArray = false) {
        $headers = $this->__getLastRequestHeaders();
        if (is_string($headers) && $asArray) {
            return self::convertStringHeadersToArray($headers);
        }
        return $headers;
    }

    /**
     * Returns the last response headers used by the SoapClient as the original value or an array
     *
     * @param bool $asArray allows to get the headers in an associative array
     * @return string|array
     */
    public function getLastResponseHeaders($asArray = false) {
        $headers = $this->__getLastResponseHeaders();
        if (is_string($headers) && $asArray) {
            return self::convertStringHeadersToArray($headers);
        }
        return $headers;
    }

    /**
     * Returns a XML string content as a DOMDocument or as a formated XML string
     *
     * @param string $string
     * @param bool $asDomDocument
     * @return DOMDocument|string|null
     */
    public static function getFormatedXml($string, $asDomDocument = false) {
        if (!empty($string) && class_exists('DOMDocument')) {
            $dom = new DOMDocument('1.0', 'UTF-8');
            $dom->formatOutput = true;
            $dom->preserveWhiteSpace = false;
            $dom->resolveExternals = false;
            $dom->substituteEntities = false;
            $dom->validateOnParse = false;
            if ($dom->loadXML($string)) {
                return $asDomDocument ? $dom : $dom->saveXML();
            }
        }
        return $asDomDocument ? null : $string;
    }

    /**
     * Returns an associative array between the headers name and their respective values
     *
     * @param string $headers
     * @return array
     */
    public static function convertStringHeadersToArray($headers) {
        $lines = explode("\r\n", $headers);
        $headers = [];
        foreach ($lines as $line) {
            if (strpos($line, ':')) {
                $headerParts = explode(':', $line);
                $headers[$headerParts[0]] = trim(implode(':', array_slice($headerParts, 1)));
            }
        }
        return $headers;
    }

    /**
     * Sets a SoapHeader to send
     *
     * @param string $nameSpace SoapHeader namespace
     * @param string $name SoapHeader name
     * @param mixed $data SoapHeader data
     * @param bool $mustUnderstand
     * @param string $actor
     * @return bool true|false
     */
    public function setSoapHeader($namespace, $name, $data, $mustUnderstand = false, $actor = null) {
        $soap_headers = [
            new SoapHeader($namespace, $name, $data, $mustUnderstand, $actor),
        ];

        return $this->__setSoapHeaders($soap_headers);
    }

    /**
     * Method returning last errors occured during the calls
     *
     * @return array
     */
    public function getLastError() {
        return $this->last_error;
    }

    /**
     * Method setting last errors occured during the calls
     *
     * @param array $lastError
     * @return array
     */
    private function setLastError($lastError) {
        $this->last_error = $lastError;
    }
}

class AuthenticationInfo {
    /**
     * The AuthCode
     * Meta informations extracted from the WSDL
     * - minOccurs : 0
     * - nillable : true
     *
     * @var string
     */
    public $AuthCode;

    /**
     * The Password
     * Meta informations extracted from the WSDL
     * - minOccurs : 0
     * - nillable : true
     *
     * @var string
     */
    public $Password;

    /**
     * The UserKey
     * Meta informations extracted from the WSDL
     * - minOccurs : 0
     * - nillable : true
     *
     * @var string
     */
    public $UserKey;

    /**
     * Constructor method for AuthenticationInfo
     *
     * @param string $_authCode
     * @param string $_password
     * @param string $_userKey
     * @return AuthenticationInfo
     */
    public function __construct($_authCode, $_password, $_userKey) {
        $this->AuthCode = $_authCode;
        $this->Password = $_password;
        $this->UserKey = $_userKey;
    }
}

class GetUsers {
    /**
     * The auth
     * Meta informations extracted from the WSDL
     * - minOccurs : 0
     * - nillable : true
     *
     * @var AuthenticationInfo
     */
    public $auth;

    /**
     * The userIds
     * Meta informations extracted from the WSDL
     * - minOccurs : 0
     * - nillable : true
     *
     * @var ArrayOfguid
     */
    public $userIds;

    /**
     * Constructor method for GetUsers
     *
     * @param AuthenticationInfo $_auth
     * @param ArrayOfguid $_userIds
     */
    public function __construct($_auth = null, $_userIds = null) {
        $this->auth = $_auth;
        $this->userIds = $_userIds;
    }
}

class GetUsersResponse {
    /**
     * The GetUsersResult
     * Meta informations extracted from the WSDL
     * - minOccurs : 0
     * - nillable : true
     *
     * @var ArrayOfUser
     */
    public $GetUsersResult;

    /**
     * Constructor method for GetUsersResponse
     *
     * @param ArrayOfUser $_getUsersResult
     */
    public function __construct($_getUsersResult = null) {
        $this->GetUsersResult = $_getUsersResult;
    }
}

class ListUsers {
    /**
     * The auth
     * Meta informations extracted from the WSDL
     * - minOccurs : 0
     * - nillable : true
     *
     * @var AuthenticationInfo
     */
    public $auth;

    /**
     * The parameters
     * Meta informations extracted from the WSDL
     * - minOccurs : 0
     * - nillable : true
     *
     * @var ListUsersRequest
     */
    public $parameters;

    /**
     * The searchQuery
     * Meta informations extracted from the WSDL
     * - minOccurs : 0
     * - nillable : true
     *
     * @var string
     */
    public $searchQuery;

    /**
     * Constructor method for ListUsers
     *
     * @param AuthenticationInfo $_auth
     * @param ListUsersRequest $_parameters
     * @param string $_searchQuery
     */
    public function __construct($_auth = null, $_parameters = null, $_searchQuery = null) {
        $this->auth = $_auth;
        $this->parameters = $_parameters;
        $this->searchQuery = $_searchQuery;
    }
}

class ListUsersRequest {
    /**
     * The Pagination
     * Meta informations extracted from the WSDL
     * - minOccurs : 0
     * - nillable : true
     *
     * @var Pagination
     */
    public $Pagination;

    /**
     * The SortBy
     * Meta informations extracted from the WSDL
     * - minOccurs : 0
     *
     * @var UserSortField
     */
    public $SortBy;

    /**
     * The SortIncreasing
     * Meta informations extracted from the WSDL
     * - minOccurs : 0
     *
     * @var bool
     */
    public $SortIncreasing;

    /**
     * Constructor method for ListUsersRequest
     *
     * @param Pagination $_pagination
     * @param UserSortField $_sortBy
     * @param boolean $_sortIncreasing
     */
    public function __construct($_pagination = null, $_sortBy = null, $_sortIncreasing = null) {
        $this->Pagination = $_pagination;
        $this->SortBy = $_sortBy;
        $this->SortIncreasing = $_sortIncreasing;
    }
}

class Pagination {
    /**
     * The MaxNumberResults
     * Meta informations extracted from the WSDL
     * - minOccurs : 0
     *
     * @var int
     */
    public $MaxNumberResults;

    /**
     * The PageNumber
     * Meta informations extracted from the WSDL
     * - minOccurs : 0
     *
     * @var int
     */
    public $PageNumber;

    /**
     * Constructor method for Pagination
     *
     * @param int $_maxNumberResults
     * @param int $_pageNumber
     */
    public function __construct($_maxNumberResults = null, $_pageNumber = null) {
        $this->MaxNumberResults = $_maxNumberResults;
        $this->PageNumber = $_pageNumber;
    }
}

class ArrayOfUser {
    /**
     * The User
     * Meta informations extracted from the WSDL
     * - maxOccurs : unbounded
     * - minOccurs : 0
     * - nillable : true
     *
     * @var User
     */
    public $User;

    /**
     * Constructor method for ArrayOfUser
     *
     * @param User $_user
     */
    public function __construct($_user = null) {
        $this->User = $_user;
    }
}

class User {
    /**
     * The Email
     * Meta informations extracted from the WSDL
     * - minOccurs : 0
     * - nillable : true
     *
     * @var string
     */
    public $Email;

    /**
     * The EmailSessionNotifications
     * Meta informations extracted from the WSDL
     * - minOccurs : 0
     *
     * @var bool
     */
    public $EmailSessionNotifications;

    /**
     * The FirstName
     * Meta informations extracted from the WSDL
     * - minOccurs : 0
     * - nillable : true
     *
     * @var string
     */
    public $FirstName;

    /**
     * The GroupMemberships
     * Meta informations extracted from the WSDL
     * - minOccurs : 0
     * - nillable : true
     *
     * @var ArrayOfguid
     */
    public $GroupMemberships;

    /**
     * The LastName
     * Meta informations extracted from the WSDL
     * - minOccurs : 0
     * - nillable : true
     *
     * @var string
     */
    public $LastName;

    /**
     * The SystemRole
     * Meta informations extracted from the WSDL
     * - minOccurs : 0
     *
     * @var SystemRole
     */
    public $SystemRole;

    /**
     * The UserBio
     * Meta informations extracted from the WSDL
     * - minOccurs : 0
     * - nillable : true
     *
     * @var string
     */
    public $UserBio;

    /**
     * The UserId
     * Meta informations extracted from the WSDL
     * - minOccurs : 0
     * - pattern : [\da-fA-F]{8}-[\da-fA-F]{4}-[\da-fA-F]{4}-[\da-fA-F]{4}-[\da-fA-F]{12}
     *
     * @var string
     */
    public $UserId;

    /**
     * The UserKey
     * Meta informations extracted from the WSDL
     * - minOccurs : 0
     * - nillable : true
     *
     * @var string
     */
    public $UserKey;

    /**
     * The UserSettingsUrl
     * Meta informations extracted from the WSDL
     * - minOccurs : 0
     * - nillable : true
     *
     * @var string
     */
    public $UserSettingsUrl;

    /**
     * Constructor method for User
     *
     * @see parent::__construct()
     * @param string $_email
     * @param boolean $_emailSessionNotifications
     * @param string $_firstName
     * @param ArrayOfguid $_groupMemberships
     * @param string $_lastName
     * @param SystemRole $_systemRole
     * @param string $_userBio
     * @param string $_userId
     * @param string $_userKey
     * @param string $_userSettingsUrl
     * @return User
     */
    public function __construct(
        $_email = null,
        $_emailSessionNotifications = null,
        $_firstName = null,
        $_groupMemberships = null,
        $_lastName = null,
        $_systemRole = null,
        $_userBio = null,
        $_userId = null,
        $_userKey = null,
        $_userSettingsUrl = null
    ) {
        $this->Email = $_email;
        $this->EmailSessionNotifications = $_emailSessionNotifications;
        $this->FirstName = $_firstName;
        $this->GroupMemberships = $_groupMemberships;
        $this->LastName = $_lastName;
        $this->SystemRole = $_systemRole;
        $this->UserBio = $_userBio;
        $this->UserId = $_userId;
        $this->UserKey = $_userKey;
        $this->UserSettingsUrl = $_userSettingsUrl;
    }
}

class ArrayOfguid {
    /**
     * The guid
     * Meta informations extracted from the WSDL
     * - maxOccurs : unbounded
     * - minOccurs : 0
     * - pattern : [\da-fA-F]{8}-[\da-fA-F]{4}-[\da-fA-F]{4}-[\da-fA-F]{4}-[\da-fA-F]{12}
     *
     * @var string
     */
    public $guid;

    /**
     * Constructor method for ArrayOfguid
     *
     * @param array?string? $_guid
     */
    public function __construct($_guid = null) {
        $this->guid = $_guid;
    }
}

class GetServerVersionResponse {
    /**
     * @var stdClass
     */
    public $GetServerVersionResult;

    public function __construct($result = null) {
        $this->GetServerVersionResult = $result;
    }
}
