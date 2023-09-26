<?php
    /*
		This class is the base KCB class. All top level functions should be included here
	*/

    require("log.class.php");
    require("../../3rd-party/sendgrid-8.0.1/vendor/sendgrid-php.php");

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

    public function LogError($message)
    {
        $this->log->write($message);
    }

    public function sendEmail($toAddress, $message, $title, $html = true)
    {
        $email = new \SendGrid\Mail\Mail(); 
        $email->setFrom("web@keysteoneconcertband.com", "Keystone Concert Band");
        $email->setSubject("Sending with SendGrid is Fun");
        $email->addTo($toAddress);
        $email->addContent($message);

        $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
        try {
            $response = $sendgrid->send($email);
            return $response->statusCode();
        } catch (Exception $e) {
            $this->LogError('Caught exception: '. $e->getMessage() ."\n");
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

        // Set admin email address errors should go to
        $admin_email = 'webmaster@keystoneconcertband.com';
    }

    private function isDevEnv()
    {
        if (strpos($_SERVER['SERVER_NAME'], 'dev') !== false || strpos($_SERVER['SERVER_NAME'], 'refresh') !== false) {
            return true;
        } else {
            return false;
        }
    }
}
