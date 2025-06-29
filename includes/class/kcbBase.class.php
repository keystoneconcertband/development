<?php
/*
    This class is the base KCB class. All top level functions should be included here
*/

require_once "log.class.php";
require_once __DIR__ . "/../../3rd-party/sendgrid-8.0.1/sendgrid-php.php" ;

class KcbBase
{
    private $log;

    public function __construct()
    {
        // Show errors if dev environment
        $this->defaultSettings($this->isDevEnv());
        $this->log = new Log();

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function logError($message)
    {
        $this->log->write($message);
    }

    public function sendEmail($toAddress, $message, $title, $html = true)
    {
        // Can't email from localhost, so pretend we did
        if ($_SERVER['SERVER_NAME'] == 'localhost') {
            return true;
        }

        $email = new \SendGrid\Mail\Mail(); 
        $email->setFrom("webmaster@keystoneconcertband.com", "KCB Website");
        $email->setSubject($title);
        $email->addTo($toAddress);

        if($html) {
            $email->addContent(
                "text/html", $message
            );
        }
        else {
            $email->addContent("text/plain", $message);
        }
        $sendgrid = new \SendGrid(getenv('APPSETTING_SENDGRID_API_KEY'));
        try {
            $response = $sendgrid->send($email);

            if($response->statusCode() < 400) {
                return true;
            }
            else {
                return false;
            }
        } catch (Exception $e) {
            $this->logError('Caught exception: '. $e->getMessage() ."\n");
            return false;
        }
    }

    private function defaultSettings($showErrors)
    {
        if ($showErrors) {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(-1);
        }

        // Set timezone so that times are displayed correctly
        date_default_timezone_set('America/New_York');
    }

    private function isDevEnv()
    {
        return strpos($_SERVER['SERVER_NAME'], 'dev') !== false || strpos($_SERVER['SERVER_NAME'], 'localhost') !== false;
    }
}