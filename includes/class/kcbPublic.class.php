<?php
require_once "kcbBase.class.php";
require_once "kcbPublic.db.class.php";

class KCBPublic
{
    private $db;
    private $kcb;

    public function __construct()
    {
        // Anonymous read-only pages should not create a session, allowing HTTP caching.
        $this->setKcb(new KcbBase(false));
        $this->setDB(new KCBPublicDb());
    }

    public function getCurrentConcert()
    {
        return $this->getDb()->getCurrentConcert();
    }

    public function getConcertSchedule()
    {
        return $this->getDb()->getConcertSchedule();
    }

    public function getHomepageMessages()
    {
        return $this->getDb()->getHomepageMessages();
    }

    public function joinSubmit($joinArray)
    {
        $webUser = "JOIN_REQUEST";

        // Verify user filled out all the correct fields
        $response = $this->validateJoin($joinArray);

        if (empty($response)) {
            // Require JS and timing protections
            $response = $this->validateSpamProtection($joinArray);
        }

        if (empty($response)) {
            // Check for spam
            $response = $this->processSpam($joinArray);

            if (empty($response)) {
                try {
                    $this->getDb()->beginTransaction();

                    // Add user
                    $ipAddress = $this->getUserIpAddr();
                    $uid = $this->getDb()->addPendingUser($joinArray, $webUser, $ipAddress);

                    // Add email
                    if ($uid > 0) {
                        if ($this->getDb()->addEmail($joinArray['txtEmail'], $uid, $webUser)) {
                            // Loop through each instrument and add
                            foreach ($joinArray['chkInstrument'] as $instr) {
                                if (!$this->getDb()->addInstrument($instr, $uid, $webUser)) {
                                    $this->getDb()->rollBackTransaction();
                                    $response = "instrument_add_error";
                                    break;
                                }
                            }
                        } else {
                            $this->getDb()->rollBackTransaction();
                            $response = "email_add_error";
                        }
                    } else {
                        $this->getDb()->rollBackTransaction();
                        $response = "add_error";
                    }

                    // Everything above was successful, save the transaction and send email notification
                    if (empty($response)) {
                        $this->getDb()->executeTransaction();

                        $emailResponse = $this->processEmail($joinArray);
                        if ($emailResponse === "success") {
                            $response = "success";
                        } else {
                            $this->getKcb()->logMessage("Join request email failed: " . $emailResponse);
                            $response = "Unable to send notification email. Your request was saved.";
                        }
                    }
                } catch (Exception $e) {
                    $this->getKcb()->logMessage($e->getMessage());
                    $this->getDb()->rollBackTransaction();
                    $response = "db_error";
                }
            }
        }
        return $response;
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

    private function validateJoin($joinArray)
    {
        $response = "";

        $name = isset($joinArray['txtName']) ? trim($joinArray['txtName']) : '';
        $phone = isset($joinArray['txtPhone']) ? trim($joinArray['txtPhone']) : '';
        $email = isset($joinArray['txtEmail']) ? trim($joinArray['txtEmail']) : '';
        $playLength = isset($joinArray['txtPlayLength']) ? trim($joinArray['txtPlayLength']) : '';
        $instruments = isset($joinArray['chkInstrument']) ? $joinArray['chkInstrument'] : [];

        if ($name === '') {
            $response = "Name is required.";
        } elseif (!empty($joinArray['honeypot'])) {
            $response = "Invalid request.";
        } elseif ($phone !== '' && strlen(preg_replace('/\D/', '', $phone)) < 10) {
            $response = "Phone number must be at least 10 digits.";
        } elseif ($email === '') {
            $response = "Email is required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response = "Please enter a valid email address.";
        } elseif ($playLength === '') {
            $response = "Length of time playing is required.";
        } elseif (!is_array($instruments) || empty($instruments)) {
            $response = "Please choose at least one instrument that you play.";
        }

        return $response;
    }

    private function validateSpamProtection($joinArray)
    {
        if (empty($joinArray['jsCheck']) || $joinArray['jsCheck'] !== 'enabled') {
            return "Please enable JavaScript to submit this form.";
        }

        if (empty($joinArray['formCreatedAt']) || !ctype_digit($joinArray['formCreatedAt'])) {
            return "Invalid form submission.";
        }

        $formAge = time() - (int)$joinArray['formCreatedAt'];
        if ($formAge < 3) {
            return "Please take a moment to complete the form before submitting.";
        }

        if ($formAge > 3600) {
            return "The form session has expired. Please refresh the page and try again.";
        }

        if (!empty($_SERVER['HTTP_REFERER'])) {
            $refererHost = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
            if ($refererHost && stripos($refererHost, $_SERVER['SERVER_NAME']) === false) {
                return "Invalid form submission source.";
            }
        }

        return null;
    }

    private function processEmail($joinArray)
    {
        # Get server variables
        $name = isset($joinArray["txtName"]) ? $joinArray["txtName"] : 'Not Provided';
        $phone = empty($joinArray["txtPhone"]) ? "Not Provided" : $joinArray["txtPhone"];
        $email = isset($joinArray["txtEmail"]) ? $joinArray["txtEmail"] : 'Not Provided';
        $instruments = "Not Provided";
        if (!empty($joinArray['chkInstrument'])) {
            if (is_array($joinArray['chkInstrument'])) {
                $instruments = implode(', ', $joinArray['chkInstrument']);
            } else {
                $instruments = (string)$joinArray['chkInstrument'];
            }
        }
        $playLength = isset($joinArray["txtPlayLength"]) ? $joinArray["txtPlayLength"] : 'Not Provided';
        $comments = empty($joinArray["txtComments"]) ? "None provided" : $joinArray["txtComments"];

        $message = "KCB Join Request Submitted<br>";
        $message .= "<b>Name</b> " . $name . "<br>";
        $message .= "<b>Phone</b> " . $phone . "<br>";
        $message .= "<b>Email</b> " . $email . "<br>";
        $message .= "<b>Instrument(s)</b> " . $instruments . "<br>";
        $message .= "<b>Length of Play</b> " . $playLength . "<br>";
        $message .= "<b>Comments</b> " . $comments;

        # Send email
        if ($this->kcb->sendEmail("webmaster@keystoneconcertband.com", $message, "KCB Join Request")) {
            $response = "success";
        } else {
            $response = "Unable to save request. Please try again later.";
        }

        return $response;
    }

    private function processSpam($joinArray)
    {
        // We've not gotten a lot of SPAM sent, but the times we've gotten it,
        // it's filled up the database with a lot of junk and hit the email limit.
        // 1. Prevent dups of the user who just tried to submit
        // 2. Check repeated submissions from the same IP or email
        // 3. Detect disposable email addresses
        // Return nothing if no spam submission is detected

        $email = isset($joinArray['txtEmail']) ? trim($joinArray['txtEmail']) : '';
        if ($email !== '') {
            if ($this->isDisposableEmail($email)) {
                return "Please use a valid email address.";
            }
            if ($this->getDb()->checkDupPendingUser($email)) {
                return "Looks like you already submitted a request to join.";
            }
        }

        $ipAddress = $this->getUserIpAddr();
        if ($ipAddress !== '') {
            $recentRequests = $this->getDb()->checkRecentUser();
            foreach ($recentRequests as $record) {
                if ($record["ip_address"] === $ipAddress) {
                    return "A recent request from this IP address has already been submitted.";
                }
            }

            if ($this->getDb()->countRecentSubmissionsByIp($ipAddress, '1 HOUR') >= 4) {
                return "Too many requests from this IP address. Please wait an hour and try again.";
            }
        }

        if ($email !== '' && $this->getDb()->countRecentSubmissionsByEmail($email, '1 DAY') >= 2) {
            return "Too many requests using this email address. Please try again later.";
        }

        return null;
    }

    private function isDisposableEmail($email)
    {
        $blockedDomains = [
            'mailinator.com',
            '10minutemail.com',
            'trashmail.com',
            'tempmail.com',
            'guerrillamail.com',
            'yopmail.com',
            'fakeinbox.com',
            'dispostable.com',
            'maildrop.cc',
        ];

        $domain = strtolower(substr(strrchr($email, '@'), 1));
        return $domain !== false && in_array($domain, $blockedDomains, true);
    }

    private function getUserIpAddr()
    {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipAddresses = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ipAddresses[0]);
        }

        return !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    }
}
