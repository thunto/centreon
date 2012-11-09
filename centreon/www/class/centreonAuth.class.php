<?php

/*
 * Copyright 2005-2011 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 * SVN : $URL$
 * SVN : $Id$
 *
 */

class CentreonAuth {
    /*
     * Declare Values
     */

    private $login;
    private $password;
    private $enable;
    private $userExists;
    private $cryptEngine;
    private $autologin;
    public $userInfos;
    private $cryptPossibilities;
    private $pearDB;
    private $debug;
    /*
     * Flags
     */
    public $passwdOk;
    private $authType;
    protected $ldap_auto_import;
    protected $ldap_store_password;

    /*
     * keep log class
     */
    private $CentreonLog;

    /*
     * Error Message
     */
    private $error;

    /**
     * Constructor
     * 
     * @param string $username
     * @param string $password
     * @param int $autologin
     * @param CentreonDB $pearDB
     * @param CentreonLog $CentreonLog
     * @param int $encryptType
     * @return void
     */
    function CentreonAuth($username, $password, $autologin, $pearDB, $CentreonLog, $encryptType = 1) {
        global $centreon_crypt;

        $this->cryptPossibilities = array('MD5', 'SHA1');
        $this->CentreonLog = $CentreonLog;
        $this->login = $username;
        $this->password = $password;
        $this->pearDB = $pearDB;
        $this->autologin = $autologin;
        $this->cryptEngine = $encryptType;
        $this->debug = $this->getLogFlag();
        $this->ldap_auto_import = array();
        $this->ldap_store_password = array();
        
        $query = "SELECT ar.ar_id, ari.ari_value, ari.ari_name 
                  FROM auth_ressource_info ari, auth_ressource ar
                  WHERE ari_name IN ('ldap_auto_import', 'ldap_store_password')
                  AND ari.ar_id = ar.ar_id
                  AND ar.ar_enable = '1'";
        $res = $pearDB->query($query);
        while ($row = $res->fetchRow()) {
            if ($row['ari_name'] == 'ldap_auto_import') {
                $this->ldap_auto_import[$row['ari_id']] = $row['ari_value'];
            } elseif ($row['ari_name'] == 'ldap_store_password') {
                $this->ldap_store_password[$row['ari_id']] = $row['ari_value'];
            }
        }
        $this->checkUser($username, $password);
    }

    /**
     * Log enabled
     * 
     * @return int
     */
    private function getLogFlag() {
        $res = $this->pearDB->query("SELECT value FROM options WHERE `key` = 'debug_auth'");
        $data = $res->fetchRow();
        if (isset($data["value"])) {
            return $data["value"];
        }
        return 0;
    }

    /**
     * Check if password is ok
     * 
     * @param string $password
     * @return void
     */
    private function checkPassword($password) {
        global $centreon_path;

        if (strlen($password) == 0 || $password == "") {
            $this->passwdOk = 0;
            return;
        }

        if ($this->userInfos["contact_auth_type"] == "ldap" && $this->autologin == 0) {

            /*
             * Insert LDAP Class
             */
            include_once ($centreon_path . "/www/class/centreonAuth.LDAP.class.php");

            $query = "SELECT ar_id FROM auth_ressource WHERE ar_enable = '1'";
            $res = $this->pearDB->query($query);
            $authResources = array();
            while ($row = $res->fetchRow()) {
                $index = $row['ar_id'];
                if (isset($this->userInfos['ar_id']) && $this->userInfos['ar_id'] == $row['ar_id']) {
                    $index = 0;
                }
                $authResources[$index] = $row['ar_id'];
            }
            
            foreach ($authResources as $arId) {
                if ($this->passwdOk == 1) {
                    break;
                }
                $authLDAP = new CentreonAuthLDAP($this->pearDB, $this->CentreonLog, $this->login, $this->password, $this->userInfos, $arId);
                $this->passwdOk = $authLDAP->checkPassword();
                if ($this->passwdOk == -1) {
                    if (isset($this->userInfos["contact_passwd"]) && $this->userInfos["contact_passwd"] == $password && $this->autologin) {
                        $this->passwdOk = 1;
                        if (isset($this->ldap_store_password[$row['ar_id']]) && $this->ldap_store_password[$row['ar_id']]) {
                            $this->pearDB->query("UPDATE `contact` SET `contact_passwd` = '" . $this->myCrypt($this->password) . "' WHERE `contact_alias` = '" . $this->login . "' AND `contact_register` = '1'");
                        }
                    } elseif (isset($this->userInfos["contact_passwd"]) && $this->userInfos["contact_passwd"] == $this->myCrypt($password) && $this->autologin == 0) {
                        $this->passwdOk = 1;
                        if (isset($this->ldap_store_password[$row['ar_id']]) && $this->ldap_store_password[$row['ar_id']]) {
                            $this->pearDB->query("UPDATE `contact` SET `contact_passwd` = '" . $this->myCrypt($this->password) . "' WHERE `contact_alias` = '" . $this->login . "' AND `contact_register` = '1'");
                        }
                    } else {
                        $this->passwdOk = 0;
                    }
                } elseif ($this->passwdOk == 1) {
                    if (isset($this->ldap_store_password[$row['ar_id']]) && $this->ldap_store_password[$row['ar_id']]) {
                        if (!isset($this->userInfos["contact_passwd"])) {
                            $this->pearDB->query("UPDATE `contact` SET `contact_passwd` = '" . $this->myCrypt($this->password) . "' WHERE `contact_alias` = '" . $this->login . "' AND `contact_register` = '1'");
                        } elseif ($this->userInfos["contact_passwd"] != $this->myCrypt($this->password)) {
                            $this->pearDB->query("UPDATE `contact` SET `contact_passwd` = '" . $this->myCrypt($this->password) . "' WHERE `contact_alias` = '" . $this->login . "' AND `contact_register` = '1'");
                        }
                    }
                }
                $cnt++;
            }
        } elseif ($this->userInfos["contact_auth_type"] == "" || $this->userInfos["contact_auth_type"] == "local" || $this->autologin) {
            if ($this->userInfos["contact_passwd"] == $password && $this->autologin) {
                $this->passwdOk = 1;
            } elseif ($this->userInfos["contact_passwd"] == $this->myCrypt($password) && $this->autologin == 0) {
                $this->passwdOk = 1;
            } else {
                $this->passwdOk = 0;
            }
        }
        if ($this->passwdOk == 2) {
            if (isset($this->userInfos["contact_passwd"]) && $this->userInfos["contact_passwd"] == $password && $this->autologin) {
                $this->passwdOk = 1;
            } elseif (isset($this->userInfos["contact_passwd"]) && $this->userInfos["contact_passwd"] == $this->myCrypt($password) && $this->autologin == 0) {
                $this->passwdOk = 1;
            } else {
                $this->passwdOk = 0;
            }
        }
    }

    /**
     * Check user password
     * 
     * @param string $username
     * @param string $password
     * @return void
     */
    private function checkUser($username, $password) {
        if ($this->autologin == 0) {
            $DBRESULT = $this->pearDB->query("SELECT * FROM `contact` WHERE `contact_alias` = '" . htmlentities($username, ENT_QUOTES, "UTF-8") . "' AND `contact_activate` = '1' AND `contact_register` = '1' LIMIT 1");
        } else {
            $DBRESULT = $this->pearDB->query("SELECT * FROM `contact` WHERE MD5(contact_alias) = '" . htmlentities($username, ENT_QUOTES, "UTF-8") . "' AND `contact_activate` = '1' AND `contact_register` = '1' LIMIT 1");
        }
        if ($DBRESULT->numRows()) {
            $this->userInfos = $DBRESULT->fetchRow();
            if ($this->userInfos["contact_oreon"]) {
                /*
                 * Check password matching
                 */
                $this->getCryptFunction();
                $this->checkPassword($password);

                if ($this->passwdOk == 1) {
                    $this->CentreonLog->setUID($this->userInfos["contact_id"]);
                    if ($this->debug) {
                        $this->CentreonLog->insertLog(1, "Contact '" . $username . "' logged in - IP : " . $_SERVER["REMOTE_ADDR"]);
                    }
                } else {
                    if ($this->debug) {
                        $this->CentreonLog->insertLog(1, "Contact '" . $username . "' doesn't match with password");
                    }
                    $this->error = "Invalid user";
                }
            } else {
                if ($this->debug) {
                    $this->CentreonLog->insertLog(1, "Contact '" . $username . "' is not enable for reaching centreon");
                }
                $this->error = "Invalid user";
            }
        } elseif (count($this->ldap_auto_import)) {
            /*
             * Add temporary userinfo auth_type
             */
            $this->userInfos['contact_alias'] = $username;
            $this->userInfos['contact_auth_type'] = "ldap";
            $this->checkPassword($password);
            /*
             * Reset userInfos with imported informations
             */
            $DBRESULT = $this->pearDB->query("SELECT * FROM `contact` WHERE `contact_alias` = '" . htmlentities($username, ENT_QUOTES, "UTF-8") . "' AND `contact_activate` = '1' AND `contact_register` = '1' LIMIT 1");
            if ($DBRESULT->numRows()) {
                $this->userInfos = $DBRESULT->fetchRow();
            }
        } else {
            if ($this->debug) {
                $this->CentreonLog->insertLog(1, "No contact found with this login : '$username'");
            }
            $this->error = "Invalid user";
        }
    }

    /*
     * Check crypt system
     */

    private function getCryptFunction() {
        if (isset($this->cryptEngine)) {
            switch ($this->cryptEngine) {
                case 1 :
                    return "MD5";
                    break;
                case 2 :
                    return "SHA1";
                    break;
                default :
                    return "MD5";
                    break;
            }
        } else {
            return "MD5";
        }
    }

    /*
     * Crypt String
     */

    private function myCrypt($str) {
        switch ($this->cryptEngine) {
            case 1 :
                return md5($str);
                break;
            case 2 :
                return sha1($str);
                break;
            default :
                return md5($str);
                break;
        }
    }

    private function getCryptEngine() {
        return $this->cryptEngine;
    }

    private function userExists() {
        return $this->userExists;
    }

    private function userIsEnable() {
        return $this->enable;
    }

    private function passwordIsOk() {
        return $this->passwdOk;
    }

    private function getAuthType() {
        return $this->authType;
    }

}

?>