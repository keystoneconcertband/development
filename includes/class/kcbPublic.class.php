<?php
require_once("kcbBase.class.php");
require_once("kcbPublic.db.class.php");

class KCBPublic
{
    private $db;
    private $kcb;

    public function __construct()
    {
        $this->setKcb(new KcbBase());
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
            // Check for spam
            $response = $this->processSpam($joinArray);

            // If we successfully sent the email, add the user to the database as a pending user
            if (empty($response)) {
                try {
                    $this->getDb()->beginTransaction();

                    // Add user
                    $ipAddress = $this->getUserIpAddr();
                    $uid = $this->getDb()->addPendingUser($joinArray, $webUser, $ipAddress);

                    // Add email
                    if ($uid > 0) {
                        if ($this->getDb()->addEmail($joinArray['txtEmail'], $uid, $webUser)) {
                            // Loop through each instrument	and add
                            foreach ($joinArray['chkInstrument'] as $instr) {
                                if (!$this->getDb()->addInstrument($instr, $uid, $webUser)) {
                                    $this->getDb()->rollBackTransaction();
                                    $response = "instrument_add_error";
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

                    // Everything above was successful, save the transaction
                    if (empty($response)) {
                        $response = "success";
                        $this->getDb()->executeTransaction();

                        // Send the email with the users request
                        $this->processEmail($joinArray);
                    }
                } catch (Exception $e) {
                    $this->getKcb()->logError($e->getMessage());
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

        if (!isset($joinArray['txtName'])) {
            $response = "Name is required.";
        } elseif (!empty($joinArray['txtPhone']) && strlen($joinArray['txtPhone']) < 10) {
            $response = "Phone number must be 10 digits.";
        } elseif (!isset($joinArray['txtEmail'])) {
            $response = "Email is required.";
        } elseif (!isset($joinArray['txtPlayLength'])) {
            $response = "Length of time playing is required.";
        } elseif (!isset($joinArray['chkInstrument'])) {
            $response = "Please choose at least one instrument that you play.";
        }

        return $response;
    }

    private function processEmail($joinArray)
    {
        # Get server variables
        $name = $joinArray["txtName"];
        $phone = empty($joinArray["txtPhone"]) ? "Not Provided" : $joinArray["txtPhone"];
        $email = $joinArray["txtEmail"];
        $instruments = implode(', ', $joinArray['chkInstrument']);
        $playLength = $joinArray["txtPlayLength"];
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
        // it's filled up the database with a lot of junk and hit the sendgrid limit.
        // 1. Prevent dups of the user who just tried to submit
        // 2. Check if request is from the same IP Address as was just entered
        // Return nothing if no spam submission is detected

        $email = $this->getDb()->checkDupPendingUser($joinArray['txtEmail']);

        if($email) {
            return "Looks like you already submitted a request to join.";
        }

        // Recent request was submitted
        $ipAddress = $this->getUserIpAddr();
        $recentRequests = $this->getDb()->checkRecentUser();

        foreach ($recentRequests as $record) {
            if($record["ip_address"] === $ipAddress) {
                return "A recent request from this IP Address has already been submitted.";
            }
        }

        return null;
    }

    private function getUserIpAddr() {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipAddresses = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ipAddresses[0]);
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }
}