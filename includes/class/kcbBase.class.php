<?php
/*
    This class is the base KCB class. All top level functions should be included here
*/

require_once "kcbLogger.class.php";
require_once "log.class.php";
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class KcbBase
{
    /** @var KcbLogger */
    private $logger;

    /** @var Log */
    private $log;

    /** @var bool Flag tracking whether global error handlers are registered */
    private static $handlersRegistered = false;

    public function __construct($startSession = true)
    {
        $this->logger = KcbLogger::getInstance();
        $this->log = new Log();

        // Show errors if dev environment
        $this->defaultSettings($this->isDevEnv());

        if ($startSession && session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Backward-compatible log message method with optional level & context
     *
     * @param string|\Throwable $message
     * @param string $level
     * @param array $context
     */
    public function logMessage($message, $level = 'error', array $context = [])
    {
        $this->logger->log($level, $message, $context);
    }

    public function logDebug($message, array $context = [])    { $this->logger->debug($message, $context); }
    public function logInfo($message, array $context = [])     { $this->logger->info($message, $context); }
    public function logNotice($message, array $context = [])   { $this->logger->notice($message, $context); }
    public function logWarning($message, array $context = [])  { $this->logger->warning($message, $context); }
    public function logError($message, array $context = [])    { $this->logger->error($message, $context); }
    public function logCritical($message, array $context = []) { $this->logger->critical($message, $context); }

    public function sendEmail($toAddress, $message, $title, $html = true)
    {
        $serverName = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '';

        // Can't email from localhost unless BREVO credentials are explicitly provided for testing
        if ($serverName === 'localhost' && empty(getenv('APPSETTING_BREVO_USERNAME'))) {
            $this->logger->info("Email sending simulated on localhost", ['to' => $toAddress, 'subject' => $title]);
            return true;
        }

        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_OFF;                      //Enable verbose debug output with DEBUG_SERVER
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = 'smtp-relay.brevo.com';                 //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = getenv('APPSETTING_BREVO_USERNAME');    //SMTP username
            $mail->Password   = getenv('APPSETTING_BREVO_PASSWORD');    //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;            //Enable implicit TLS encryption
            $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom('jonathang@keystoneconcertband.com', 'KCB Website');
            $mail->addAddress($toAddress);

            //Content
            $mail->isHTML($html);
            $mail->Subject = $title;
            $mail->Body    = $message;

            $mail->send();
            $this->logger->info("Email dispatched successfully", ['to' => $toAddress, 'subject' => $title]);
            return true;
        } catch (Exception $e) {
            $this->logger->error("Message could not be sent. Mailer Error: {$mail->ErrorInfo}", [
                'to'      => $toAddress,
                'subject' => $title,
                'error'   => $mail->ErrorInfo,
            ]);
            return false;
        }
    }

    private function defaultSettings($showErrors)
    {
        if ($showErrors) {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(-1);
        } else {
            ini_set('display_errors', 0);
            ini_set('display_startup_errors', 0);
            error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
        }

        // Set timezone so that times are displayed correctly
        date_default_timezone_set('America/New_York');

        $this->registerGlobalHandlers();
    }

    /**
     * Register global exception, error, and shutdown handlers
     */
    private function registerGlobalHandlers()
    {
        if (self::$handlersRegistered) {
            return;
        }
        self::$handlersRegistered = true;

        $logger = $this->logger;

        // Global uncaught exception handler
        set_exception_handler(function (\Throwable $throwable) use ($logger) {
            $logger->critical("Uncaught Exception: " . $throwable->getMessage(), [
                'exception' => $throwable,
            ]);

            if (!headers_sent()) {
                http_response_code(500);
            }

            // Return JSON if an API call or request accepts JSON
            $isApi = (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api/') !== false)
                || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

            if ($isApi) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'An internal server error occurred. The incident has been logged.']);
            } else {
                echo "<h1>Internal Server Error</h1><p>An unexpected error occurred. The incident has been logged.</p>";
            }
        });

        // Global PHP error/warning handler
        set_error_handler(function ($severity, $message, $file, $line) use ($logger) {
            if (!(error_reporting() & $severity)) {
                return false;
            }

            $context = ['file' => $file, 'line' => $line];

            switch ($severity) {
                case E_USER_ERROR:
                case E_RECOVERABLE_ERROR:
                    $logger->error("PHP Error: $message", $context);
                    break;
                case E_WARNING:
                case E_USER_WARNING:
                    $logger->warning("PHP Warning: $message", $context);
                    break;
                case E_NOTICE:
                case E_USER_NOTICE:
                    $logger->notice("PHP Notice: $message", $context);
                    break;
                default:
                    $logger->debug("PHP Note ($severity): $message", $context);
                    break;
            }

            return false; // Allow standard PHP handler flow
        });

        // Fatal shutdown handler
        register_shutdown_function(function () use ($logger) {
            $lastError = error_get_last();
            if ($lastError && in_array($lastError['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
                $logger->emergency("Fatal PHP Shutdown: " . $lastError['message'], [
                    'file' => $lastError['file'],
                    'line' => $lastError['line'],
                    'type' => $lastError['type'],
                ]);
            }
        });
    }

    private function isDevEnv()
    {
        $serverName = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '';
        return strpos($serverName, 'dev') !== false || strpos($serverName, 'localhost') !== false;
    }
}
