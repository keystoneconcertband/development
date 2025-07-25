<?php
/*
    This class is the base KCB class. All top level functions should be included here
*/

require_once "log.class.php";
require_once __DIR__ . "/../../3rd-party/sendgrid-8.0.1/sendgrid-php.php" ;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '/../../3rd-party/PHPMailer-6.10/Exception.php';
require_once '/../../3rd-party/PHPMailer-6.10/PHPMailer.php';
require_once '/../../3rd-party/PHPMailer-6.10/SMTP.php';

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

        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = 'smtp-relay.brevo.com';                 //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = getenv('APPSETTING_BREVO_USERNAME');    //SMTP username
            $mail->Password   = getenv('APPSETTING_BREVO_PASSWORD');    //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom('jonathang@keystoneconcertband.com', 'Mailer');
            $mail->addAddress($toAddress);

            //Content
            $mail->isHTML($html);
            $mail->Subject = $title;
            $mail->Body    = $message;

            $mail->send();
            return true;
        } catch (Exception $e) {
            $this->logError("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
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