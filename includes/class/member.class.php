<?php
// kcbBase is its parent
require_once "kcbBase.class.php";
require_once "member.db.class.php";

class Member
{
    const MAX_EXPIRE = 30;
    private $kcbCookie = "KCB_Cookie";
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
                exit;
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
                    $sendResponse = $this->sendAuthRequest($email);
                    if ($sendResponse === "auth_required_no_cookie") {
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

    // Verify auth cd
    public function verifyAuthCd($email, $auth_cd, $auth_remember)
    {
        // Verify user is still valid
        $response = $this->isValidUser($email);

        if ($response !== "valid") {
            $this->getDb()->logLogin($email, $response);
            return $response;
        }

        $ipAddress = $this->getIpAddress();
        $authCdDb = $this->getDb()->getAuthCd($email);

        // See if auth_cd matches
        if ($auth_cd !== $authCdDb['auth_cd']) {
            if ($this->upInvalidCdCount($email) == "db_error") {
                $response = "db_error";
            } else {
                $response = "auth_invalid";
            }

            $this->getDb()->logLogin($email, $response);
            return $response;
        }

        // See if code is from within the last MAX_EXPIRE mins
        $authCdTimestamp = $this->getDb()->getAuthCdTimestamp($email);
        if ($authCdTimestamp === false || $authCdTimestamp === null) {
            $this->getKcb()->logMessage("Invalid auth timestamp for email: " . $email . " value: " . var_export($authCdDb['lst_tran_dt_tm'], true));
            $response = "auth_old";
            $this->getDb()->logLogin($email, $response);
            return $response;
        }

        $authCdDtTm = $authCdTimestamp + 60 * self::MAX_EXPIRE;
        $this->getKcb()->logMessage("AuthCdDtTm: " . date('Y-m-d h:i:sa', $authCdDtTm) . " (" . $authCdDtTm . ") Current Time: " . time() . " (" . date('Y-m-d h:i:sa', time()) . ") Email: " . $email);

        if (time() > $authCdDtTm) {
            if ($this->sendAuthRequest($email)) {
                $response = "auth_old";
            } else {
                $response = "db_error";
            }

            $this->getDb()->logLogin($email, $response);
            return $response;
        }

        // Create auth_cd_guid for cookie
        $guid = $this->guid();

        // Update user's account
        if (!$this->getDb()->setAuthCd($email, $guid)) {
            $response = "db_error";
        } elseif (!$this->getDb()->updateLastLogin($email)) {
            $response = "db_error";
        } else {
            $this->saveSession($email, $guid);
            if ($auth_remember == "true") {
                $this->saveCookie($email, $guid);
            }
            $response = "valid";
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

        if (!$member || !isset($member['disabled']) || $member['disabled'] !== 0) {
            $response = "invalid";
        } else {
            if ($member['accountType'] === 3) {
                $response = "invalid_pending";
            } else {
                $accountLocked = $this->getDb()->accountLockedStatus($email);

                if ($accountLocked != '') {
                    $response =  "over_max_requests__" . date('D, M j g:i A', strtotime($accountLocked) + 3600);
                }
            }
        }

        return $response;
    }

    // Sends email request. Disabled texting in 2024 due to issues with sending texts.
    private function sendAuthRequest($email)
    {
        $member = $this->getDb()->getMember($email);
        $response = $this->sendAuthEmail($email, $member);
        return $response;
    }

    // Send Auth Emails
    private function sendAuthEmail($email, $member)
    {
        $six_digit_random_number = mt_rand(100000, 999999);
        $response = $this->authCodeLogic($email, $six_digit_random_number);

        // If valid
        if ($response == "auth_required_no_cookie") {
            // Add MAX_EXPIRE time to the current time to show in the email when the code is valid until.
            $date = new DateTime(date('Y-m-d h:i:sa'));
            $dateInterval = "PT" . self::MAX_EXPIRE . "M";
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
            $authCdTimestamp = $this->getDb()->getAuthCdTimestamp($email);
            if ($authCdTimestamp === false || $authCdTimestamp === null) {
                $response = "db_error";
            } else {
                $authCdDtTm = $authCdTimestamp + 60 * self::MAX_EXPIRE;

                // Don't send another email if its been less than MAX_EXPIRE mins
                if (time() <= $authCdDtTm) {
                    $response = "auth_cd_not_expired";
                } elseif (!$this->getDb()->setLoginCd($member['UID'], $six_digit_random_number, "0", $ipAddress)) {
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
        $secure = true;
        if (isset($_SERVER['HTTP_HOST']) && preg_match('/^(localhost|127\.0\.0\.1|\[::1\])(:\d+)?$/i', $_SERVER['HTTP_HOST'])) {
            $secure = false;
        }

        $cookieOptions = [
            'expires' => time() + (60 * 60 * 24 * 365),
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Strict',
        ];

        setcookie($this->kcbCookie, $email . "," . $auth_cd, $cookieOptions);
    }

    // Determines whether or not the cookie passed in from the client contains the valid auth code
    private function isValidAuthCookie($email)
    {
        $response = false;

        if (isset($_COOKIE[$this->kcbCookie])) {
            $pieces = explode(",", $_COOKIE[$this->kcbCookie], 2);
            if (count($pieces) === 2) {
                $cookieEmail = $pieces[0];
                $cookieAuthCd = $pieces[1];

                // Email must match the cookieEmail
                if ($email == $cookieEmail) {
                    // Only check if the cookie email matches the email the user is logging in from
                    $auth_cd_guid = $this->getDb()->getAuthCdGuid($email, $cookieAuthCd);
                    $response = $auth_cd_guid != null;
                }
            }
        }

        return $response;
    }

    private function getAuthCdFromCookie()
    {
        $authCd = "";
        if (isset($_COOKIE[$this->kcbCookie])) {
            $pieces = explode(",", $_COOKIE[$this->kcbCookie], 2);
            if (count($pieces) === 2) {
                $authCd = $pieces[1];
            }
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
}
