<?php

require_once(INCLUDE_DIR . 'class.auth.php');

class StaffVatsimUKAuthentication extends StaffAuthenticationBackend {

    static $name = "VATSIM Authentication";
    static $id = "vatsimuk";

    function supportsInteractiveAuthentication() {
        return false;
    }

    function signOn() {
        $config = new Config("core");
        $URL = $config->get("helpdesk_url");

        // Let's login via SSO!
        require_once "/home/shared/shared.vatsim.uk/SSO.class.php";
        $SSO = new SSO(VATUK_AUTH_SCP_ID, VATUK_AUTH_SCP_SECRET, $URL."/scp/login.php");
        $SSO->authenticate();
        $member = $SSO->member;

        if ($member && isset($member->cid)) {
            if (($user = StaffSession::lookup(array('username' => 's' . $member->cid))) && $user->getId()) {
                return $user;
            }
        }
    }

}

class UserVatsimUKAuthentication extends UserAuthenticationBackend {

    static $name = "VATSIM Authentication";
    static $id = "vatsimuk.client";

    function supportsInteractiveAuthentication() {
        return false;
    }

    function signOn() {
        $config = new Config("core");
        $URL = $config->get("helpdesk_url");

        // Let's login via SSO!
        require_once "/home/shared/shared.vatsim.uk/SSO.class.php";
        $SSO = new SSO(VATUK_AUTH_ID, VATUK_AUTH_SECRET, $URL."/login.php");
        $SSO->authenticate();
        $member = $SSO->member;

        if ($member && isset($member->cid)) {
            // Try and find the account by their username....
            $acct = ClientAccount::lookupByUsername($member->cid);
            if ($acct = ClientAccount::lookupByUsername($member->cid)) {
                if (($client = new ClientSession(new EndUser($acct->getUser()))) && $client->getId()) {
                    $user = $acct->getUser();
                    $oldAddress = $user->getDefaultEmailAddress();
                    $userID = $client->getId();

                    // Has their email changed?
                    if(strcasecmp($oldAddress, $member->email) != 0){
                        // Let's check if this email exists, first of all.
                        $newEmail = UserEmailModel::lookup(array("address" => $member->email));

                        if($newEmail){
                            // Let's update the user_id for this email!
                            $newEmail->set("user_id", $userID);
                            $newEmail->save();
                        } else {
                            // Let's add the new email.
                            $newEmail = new UserEmailModel();
                            $newEmail->set("user_id", $userID);
                            $newEmail->set("address", $member->email);
                            $newEmail->save();
                        }

                        // Update the default email ID.
                        $user->set("default_email_id", $newEmail->get("id"));
                        $user->save();
                    }

                    return $client;
                }
            } else { // Doesn't exist, so let's make one?
                // IF the user has previously used helpdesk to submit a ticket via email (without an account) this will sync, based on email address.
                $client = new ClientCreateRequest($this, $member->cid, ["email" => $member->email, "name" => $member->name_full, "cid" => $member->cid]);
                return $client->attemptAutoRegister();
            }
        }
    }
}

require_once(INCLUDE_DIR . 'class.plugin.php');
require_once('config.php');

class VatsimUKAuthPlugin extends Plugin {

    var $config_class = 'VatsimUKAuthConfig';

    function bootstrap() {
        $config = $this->getConfig();
        if ($config->get('auth-staff'))
            StaffAuthenticationBackend::register('StaffVatsimUKAuthentication');
        if ($config->get('auth-client'))
            UserAuthenticationBackend::register('UserVatsimUKAuthentication');
    }

}
