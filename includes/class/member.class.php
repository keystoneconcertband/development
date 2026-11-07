<?php
// kcbBase is its parent
require_once "kcbBase.class.php";
require_once "member.db.class.php";

class Member
{
    const MAX_EXPIRE = 15;
    const RESEND_COOLDOWN = 60;
    private $kcbCookie = "KCB_Cookie";
    private $db;
    private $kcb;

    /* PUBLIC FUNCTIONS */
    public function __construct($authReq, $startSession = true)
    {
        $this->setKcb(new KcbBase($startSession));
        $this->setDB(new MemberDB());

        if ($authReq) {
            if (!$this->validSession()) {
                $redirectParam = '';
                if (!empty($_SERVER['REQUEST_URI'])) {
                    $redirectParam = '?redirect=' . rawurlencode($_SERVER['REQUEST_URI']);
                }
                header('Location: /members/reauth.php' . $redirectParam);
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
                $this->getKcb()->logInfo("Authentication code generated and dispatched", ['email' => $email]);
            } else {
                // Validate that the cookie auth code matches what is in the database
                if (!$this->isValidAuthCookie($email)) {
                    // Send auth email, user's cookie is bad
                    $this->getKcb()->logWarning("Login attempted with invalid auth cookie", ['email' => $email]);
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
                    $cookieGuid = $this->getAuthCdFromCookie();
                    $this->saveSession($email, $this->getDb()->getAuthCdGuid($email, $cookieGuid));
                    // Refresh 1-year sliding cookie expiration
                    $this->saveCookie($email, $cookieGuid);
                    $this->getKcb()->logInfo("Member authenticated via remember-me cookie", ['email' => $email]);
                }
            }
        } else {
            $this->getKcb()->logWarning("Login attempted for invalid/unregistered email", ['email' => $email]);
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
            $this->getKcb()->logWarning("Auth verification failed: user account not valid", ['email' => $email]);
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

            $this->getKcb()->logWarning("Invalid auth code entered", ['email' => $email, 'ip' => $ipAddress]);
            $this->getDb()->logLogin($email, $response);
            return $response;
        }

        // See if code is from within the last MAX_EXPIRE mins
        $authCdTimestamp = $this->getDb()->getAuthCdTimestamp($email);
        if ($authCdTimestamp === false || $authCdTimestamp === null) {
            $this->getKcb()->logError("Invalid auth timestamp in database", [
                'email' => $email,
                'raw'   => var_export($authCdDb['lst_tran_dt_tm'], true),
            ]);
            $response = "auth_old";
            $this->getDb()->logLogin($email, $response);
            return $response;
        }

        $authCdDtTm = $authCdTimestamp + 60 * self::MAX_EXPIRE;

        if (time() > $authCdDtTm) {
            $this->getKcb()->logNotice("Expired auth code submitted", [
                'email'     => $email,
                'code_time' => date('Y-m-d H:i:s', $authCdTimestamp),
            ]);
            $sendRes = $this->sendAuthRequest($email);
            if ($sendRes === "auth_required_no_cookie" || strpos($sendRes, "resend_throttle") === 0) {
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
            $this->getKcb()->logError("Failed to set auth GUID in database", ['email' => $email]);
            $response = "db_error";
        } elseif (!$this->getDb()->updateLastLogin($email)) {
            $this->getKcb()->logError("Failed to update last login timestamp in database", ['email' => $email]);
            $response = "db_error";
        } else {
            $this->saveSession($email, $guid);
            if ($auth_remember == "true") {
                $this->saveCookie($email, $guid);
            }
            $this->getKcb()->logInfo("Member verified auth code and logged in", [
                'email'       => $email,
                'remember_me' => ($auth_remember == "true"),
            ]);
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
        $six_digit_random_number = (string)random_int(100000, 999999);
        $response = $this->authCodeLogic($email, $six_digit_random_number);

        // If valid to send email
        if ($response == "auth_required_no_cookie") {
            $date = new DateTime();
            $date->add(new DateInterval("PT" . self::MAX_EXPIRE . "M"));

            $subject = "Keystone Concert Band Login Code";
            $message = "Hi <b>" . htmlspecialchars($member['firstName'] ?? '', ENT_QUOTES, 'UTF-8') . "</b>,<br><br>";
            $message .= "A login code has been requested to access the members section of www.keystoneconcertband.com using your email address, <b>";
            $message .= htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
            $message .= "</b>. To continue on the website, please enter the login code below:<br><br>";
            $message .= "<span style='font-size:24px; font-weight:bold; letter-spacing:4px; background:#f4f4f4; padding:8px 16px; border-radius:4px;'>";
            $message .= $six_digit_random_number;
            $message .= "</span><br><br>";
            $message .= "Please note, this code is valid for " . self::MAX_EXPIRE . " minutes (until " . $date->format('g:i A') . "), and you have 3 tries to enter it successfully. ";
            $message .= "If you enter an incorrect code 3 times within an hour, your account will be locked out for 1 hour.<br><br>";
            $message .= "If you did not request to log in to the website, you can safely ignore this email.<br><br>";
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
                $secondsSinceLast = time() - (int)$authCdTimestamp;

                // Enforce 60-second cooldown between generating/sending new codes
                if ($secondsSinceLast < self::RESEND_COOLDOWN) {
                    $remaining = self::RESEND_COOLDOWN - $secondsSinceLast;
                    $response = "resend_throttle__" . $remaining;
                } elseif (!$this->getDb()->setLoginCd($member['UID'], $six_digit_random_number, "0", $ipAddress)) {
                    $response = "db_error";
                }
            }
        } else {
            // User's first time logging in, just insert a new record
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

    // Revoke remember-me cookie and remove token from database
    public function revokeRememberMeCookie($email = null)
    {
        if ($email === null && isset($_SESSION['email'])) {
            $email = $_SESSION['email'];
        }

        $cookieAuthCd = $this->getAuthCdFromCookie();
        if ($email) {
            $this->getDb()->deleteAuthCdGuid($email, $cookieAuthCd ?: null);
        }

        $secure = true;
        if (isset($_SERVER['HTTP_HOST']) && preg_match('/^(localhost|127\.0\.0\.1|\[::1\])(:\d+)?$/i', $_SERVER['HTTP_HOST'])) {
            $secure = false;
        }

        $cookieOptions = [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Strict',
        ];

        setcookie($this->kcbCookie, '', $cookieOptions);
        if (isset($_COOKIE[$this->kcbCookie])) {
            unset($_COOKIE[$this->kcbCookie]);
        }
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

        if ($invCount >= 3) {
            $this->getKcb()->logWarning("Repeated invalid auth code attempts detected", [
                'email'         => $email,
                'invalid_count' => $invCount,
                'ip'            => $ipAddress,
            ]);
        }

        // Update login cd invalid_count
        if (!$this->getDb()->setLoginCdInvalidCount($email, $ipAddress, strval($invCount))) {
            // Update login count and last login date.
            $this->getDb()->updateLastLogin($email);

            $response = "db_error";
        }

        return $response;
    }

    // Calculates cryptographically secure RFC 4122 UUID v4
    private function guid()
    {
        if (function_exists('com_create_guid') === true) {
            return trim(com_create_guid(), '{}');
        }

        $bytes = random_bytes(16);
        $bytes[6] = chr(ord($bytes[6]) & 0x0f | 0x40); // set version to 0100
        $bytes[8] = chr(ord($bytes[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }

    // Save session information and regenerate session ID to prevent fixation
    private function saveSession($email, $guid)
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

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
