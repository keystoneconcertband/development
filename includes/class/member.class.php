<?php
    // kcbBase is its parent
    include_once("kcbBase.class.php");
    include_once("member.db.class.php");

class Member
{
    private $MAX_EXPIRE = 30;
    private $kcbCookie = "KCB_Cookie";
    private $fbAuthCookie = "fbsr_129894350764";
    private $db;
    private $kcb;

    /* PUBLIC FUNCTIONS */
    public function __construct($authReq)
    {
        $this->setKcb(new KcbBase());
        $this->setDB(new MemberDB());

        if ($authReq) {
            if (!$this->validSession()) {
                header('Location: reauth.php');
            }
        }
    }

    public function getIpAddress()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    // Gets the members by instrument
    public function getMembers($instrument)
    {
        return $this->getDb()->getMembers($instrument);
    }

    // Gets the current member by email
    public function getMember($email)
    {
        return $this->getDb()->getMember($email);
    }

    // Main login function
    public function login($email)
    {
        $response = $this->isValidUser($email);

        if ($response == "valid") {
            // See if auth cookie exists for the user.
            if (!isset($_COOKIE[$this->kcbCookie])) {
                // If sendText is true (default), then send a text based upon their email account.
                $response = $this->sendAuthRequest($email);
            } else {
                // Validate that the cookie auth code matches what is in the database
                if (!$this->isValidAuthCookie($email)) {
                    // Send auth email, user's cookie is bad
                    if ($this->sendAuthRequest($email) <> 'db_error') {
                        $response = "auth_failed_invalid_cookie";
                    } else {
                        $response = "db_error";
                    }
                } else {
                    // Update login count and last login date.
                    $this->getDb()->updateLastLogin($email);

                    // Save email address since user's session is now valid to continue.
                    $this->saveSession($email, $this->getDb()->getAuthCdGuid($email, $this->getAuthCdFromCookie()));
                }
            }
        }

        $this->getDb()->logLogin($email, $response);
        return $response;
    }

    // Facebook login process.
    public function facebookLogin($email, $fbId)
    {
        $response = $this->validateFbAuth();

        if ($response == "success") {
            $response = $this->isValidUser($email);

            if ($response == "valid") {
                $response = "fb_valid";

                // Update login count and last login date.
                $this->getDb()->updateLastLogin($email);

                // Save email address since user's session is now valid to continue.
                $this->saveSession($email, $fbId);
            }
        }

        $this->getDb()->logLogin($email, $response);
        return $response;
    }

    // Verify auth cd
    public function verifyAuthCd($email, $auth_cd, $auth_remember)
    {
        // Verify user is still valid
        $response = $this->isValidUser($email);

        if ($response == "valid") {
            $ipAddress = $this->getIpAddress();
            $authCdDb = $this->getDb()->getAuthCd($email);

            // See if auth_cd matches
            if ($auth_cd == $authCdDb['auth_cd']) {
                // See if code is from within the last $MAX_EXPIRE mins
                $authCdDtTm = strtotime($authCdDb['lst_tran_dt_tm']) + 60 * $this->MAX_EXPIRE;

                if (date(time()) > $authCdDtTm) {
                    if ($this->sendAuthRequest($email)) {
                        $response = "auth_old";
                    } else {
                        $response = "db_error";
                    }
                } else {
                    // Create auth_cd_guid for cookie
                    $guid = $this->guid();

                    // Update user's account
                    if (!$this->getDb()->setAuthCd($email, $guid)) {
                        $response = "db_error";
                    } else {
                        // Update login count and last login date.
                        if (!$this->getDb()->updateLastLogin($email)) {
                            $response = "db_error";
                        } else {
                            // Save email address since user's session is now valid to continue.
                            $this->saveSession($email, $guid);

                            if ($auth_remember == "true") {
                                $this->saveCookie($email, $guid);
                            }

                            $response = "valid";
                        }
                    }
                }
            } else {
                if ($this->upInvalidCdCount($email) == "db_error") {
                    $response = "db_error";
                } else {
                    $response = "auth_invalid";
                }
            }
        }

        $this->getDb()->logLogin($email, $response);
        return $response;
    }

    // Makes sure that the email and auth_cd_guid exists in the session
    public function validSession()
    {
        $validSession = false;
        if (isset($_SESSION['email']) && isset($_SESSION['auth_cd_guid'])) {
            $validSession = true;
        }

        return $validSession;
    }

    /* PRIVATE FUNCTIONS */
    private function getDb()
    {
        return $this->db;
    }

    private function setDb($db)
    {
        $this->db = $db;
    }

    private function getKcb()
    {
        return $this->kcb;
    }

    private function setKcb($kcb)
    {
        $this->kcb = $kcb;
    }

    // Gets whether or not the email address is valid, account is not disabled, and account locked status
    private function isValidUser($email)
    {
        $response = "valid";
        $member = $this->getDb()->getMember($email);

        // NOTE: 0 can only mean that the user is active. If false, the user doesn't exist or is disabled.
        if ($member['disabled'] === 0) {
            // Don't allow pending members to login
            if ($member['accountType'] === 3) {
                $response = "invalid_pending";
            }
            // Validate account auth cd isn't locked out
            $accountLocked = $this->getDb()->accountLockedStatus($email);

            if ($accountLocked != '') {
                $response =  "over_max_requests__" . date('D, M j g:i A', strtotime($accountLocked) + 3600);
            }
        } else {
            $response = "invalid";
        }

        return $response;
    }

    // If user has a text/carrier entered, send as text. If not, send as email.
    private function sendAuthRequest($email)
    {
        $response = false;
        $member = $this->getDb()->getMember($email);

        if (isset($member['text']) && $member['text'] !== "") {
            // User has texting enabled, send auth code as text
            $six_digit_random_number = mt_rand(100000, 999999);
            $AuthResponse = $this->authCodeLogic($email, $six_digit_random_number);

            // If valid
            if ($AuthResponse == "auth_required_no_cookie") {
                $message = "Your KCB Members security code is " . $six_digit_random_number . ". It will expire in " . $this->MAX_EXPIRE . " minutes.";
                $textAddress = $member['text'] . "@" . $member['carrier'];

                // Send text
                $response = $this->getKcb()->sendEmail($textAddress, $message, "KCB Login Code", false);
            }
        } else {
            // User doesn't have a text address. Send them an email.
            $response = $this->sendAuthEmail($email, $member);
        }

        return $response;
    }

    // Send Auth Emails
    private function sendAuthEmail($email, $member)
    {
        $six_digit_random_number = mt_rand(100000, 999999);
        $response = $this->authCodeLogic($email, $six_digit_random_number);

        // If valid
        if ($response == "auth_required_no_cookie") {
            // Add $MAX_EXPIRE time to the current time to show in the email when the code is valid until.
            $date = new DateTime(date('Y-m-d h:i:sa'));
            $dateInterval = "PT" . $this->MAX_EXPIRE . "M";
            $date->add(new DateInterval($dateInterval));

            $subject = "Keystone Concert Band Login Code";
            $message = "Hi <b>" . $member['firstName'] . "</b>,<br><br>";
            $message .= "A login code has been requested to login the members section of www.keystoneconcertband.com using your email address, <b>";
            $message .= $email;
            $message .= "</b>. To continue on the website, you must enter the login code provided below:<br><b>";
            $message .= $six_digit_random_number . "</b><br><br>";
            $message .= "Please note, this code is only valid until " . $date->format('Y-m-d h:ia') . ", and you will have only 3 tries to enter it successfully. ";
            $message .= "If you enter an incorrect code more than 3 times within an hour, your account will be locked out for 1 hour.<br><br>";
            $message .= "If you did not try to login the website recently, please delete this email as someone else tried to use your email address.\r\n\r\n";
            $message .= "Thanks,<br>";
            $message .= "Jonathan Gillette";

            if (!$this->getKcb()->sendEmail($email, $message, $subject)) {
                $response = "Unable to send login code email. Please try again later.";
            }
        }

        return $response;
    }

    private function authCodeLogic($email, $six_digit_random_number)
    {
        $response = "auth_required_no_cookie";
        $ipAddress = $this->getIpAddress();
        $member = $this->getDb()->getMember($email);
        $authCdDb = $this->getDb()->getAuthCd($email);

        if ($authCdDb) {
            $authCdDtTm = strtotime($authCdDb['lst_tran_dt_tm']) + 60 * $this->MAX_EXPIRE;

            // Don't send another email if its been less than $MAX_EXPIRE mins
            if (date(time()) <= $authCdDtTm) {
                $response = "auth_cd_not_expired";
            } else {
                if (!$this->getDb()->setLoginCd($member['UID'], $six_digit_random_number, "0", $ipAddress)) {
                    $response = "db_error";
                }
            }
        } else {
            // Users first time logging in, just insert a new record
            if (!$this->getDb()->setLoginCd($member['UID'], $six_digit_random_number, "0", $ipAddress)) {
                $response = "db_error";
            }
        }

        return $response;
    }

    /* Save cookie to the users system */
    private function saveCookie($email, $auth_cd)
    {
        // Set cookie with information and expiration of one year
        setcookie($this->kcbCookie, $email . "," . $auth_cd, time() + (60*60*24*365), "/");
    }

    // Determines whether or not the cookie passed in from the client contains the valid auth code
    private function isValidAuthCookie($email)
    {
        $response = false;

        if (isset($_COOKIE[$this->kcbCookie])) {
            $pieces = explode(",", $_COOKIE[$this->kcbCookie]);
            $cookieEmail = $pieces[0];
            $cookieAuthCd = $pieces[1];

            // Email must match the cookieEmail
            if ($email == $cookieEmail) {
                // Only check if the cookie email matches the email the user is logging in from
                $auth_cd_guid = $this->getDb()->getAuthCdGuid($email, $cookieAuthCd);
                $response = $auth_cd_guid != null;
            }
        }

        return $response;
    }

    private function getAuthCdFromCookie()
    {
        $authCd = "";
        if (isset($_COOKIE[$this->kcbCookie])) {
            $pieces = explode(",", $_COOKIE[$this->kcbCookie]);
            $authCd =  $pieces[1];
        }

        return $authCd;
    }

    // Increase the invalid cd count
    private function upInvalidCdCount($email)
    {
        $response = "valid";
        $invCount = $this->getDb()->getInvalidCount($email) + 1;
        $ipAddress = $this->getIpAddress();

        // Update login cd invalid_count
        if (!$this->getDb()->setLoginCdInvalidCount($email, $ipAddress, strval($invCount))) {
            // Update login count and last login date.
            $this->getDb()->updateLastLogin($email);

            $response =  "db_error";
        }

        return $response;
    }

    // Calculates GUID
    private function guid()
    {
        if (function_exists('com_create_guid') === true) {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }

    // Save session information
    private function saveSession($email, $guid)
    {
        $_SESSION["email"] = $email;
        $_SESSION["auth_cd_guid"] = $guid;

        // Get member info to store in session
        $member = $this->getDb()->getMember($email);
        $_SESSION['uid'] = $member['UID'];
        $_SESSION['accountType'] = $member['accountType'];
        $_SESSION['office'] = $member['office'];
        $_SESSION['firstName'] = $member['firstName'];
        $_SESSION['lastName'] = $member['lastName'];
    }

    private function validateFbAuth()
    {
        if (isset($_COOKIE[$this->fbAuthCookie])) {
            $fbCookie = $_COOKIE[$this->fbAuthCookie];
            list($encoded_sig, $payload) = explode('.', $fbCookie, 2);

            $this->settings = parse_ini_file("settings.ini.php");
            $secret = $this->settings["fbSecret"];

            // decode the data
            $sig = $this->base64_url_decode($encoded_sig);

            // Data: https://developers.facebook.com/docs/reference/login/signed-request
            $data = json_decode($this->base64_url_decode($payload), true);

            // confirm the signature
            $expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
            if ($sig !== $expected_sig) {
                return "sig_not_match";
            } else {
                // Verify that the issued_at + 10 mins is > now
                $inTenMinutes = $data['issued_at'] + 10 * 60;

                if ($inTenMinutes > time()) {
                    return "success";
                } else {
                    return "fb_session_hijack";
                }
            }
        } else {
            return "no_fb_cookie";
        }
    }

    public function base64_url_decode($input)
    {
        return base64_decode(strtr($input, '-_', '+/'));
    }
}
