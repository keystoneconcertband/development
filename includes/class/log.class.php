<?php
       /* *
	* Log 			A logger class which creates logs when an exception is thrown.
	* @author		Author: Vivek Wicky Aswal. (https://twitter.com/#!/VivekWickyAswal)
	* @git 			https://github.com/indieteq/PHP-MySQL-PDO-Database-Class
	* @version      0.1a
	*/
class Log
{

    # @string, Log directory name
    private $path = "";

    # @void, Default constructor
    public function __construct()
    {
        $docRoot = $_SERVER['DOCUMENT_ROOT'];
        $this->path = $docRoot . '/_logs/';
    }

   /**
            *   @void
            *   Creates the log
            *
            *   @param string $message the message which is written into the log.
            *   @description:
            *    1. Checks if directory exists, if not, create one and call this method again.
            *    2. Checks if log already exists.
            *    3. If not, new log gets created. Log is written into the logs folder.
            *    4. Logname is current date(Year - Month - Day).
            *    5. If log exists, edit method called.
            *    6. Edit method modifies the current log.
            */
    public function write($message)
    {
        $date = new DateTime();
        $log = $this->path . $date->format('Y-m-d').".txt";

        if (is_dir($this->path)) {
            if (!file_exists($log)) {
                $fh  = fopen($log, 'a+') or die("Fatal Error !");
                $logcontent = "Time : " . $date->format('H:i:s')."\r\n" . $message ."\r\n";
                fwrite($fh, $logcontent);
                fclose($fh);
            } else {
                $this->edit($log, $date, $message);
            }
        } else {
            if (mkdir($this->path, 0777) === true) {
                 $this->write($message, $page);
            }
        }

        // Send email to webmaster
        try {
            // To send HTML mail, the Content-type header must be set
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-type: text/html; charset=iso-8859-1';

            // Additional headers
            $headers[] = 'From: KCB Website <web@keystoneconcertband.com>';
            $headers[] = 'Reply-To: web@keystoneconcertband.com';
            $headers[] = 'X-Mailer: PHP/' . phpversion();

            $msg = $message . "<br>Server Variables: " . $this->getServerVars();

            mail('JonathanG@keystoneconcertband.com', 'KCB Web Error', $msg, implode("\r\n", $headers));
        } catch (Exception $e) {
            // Don't do anything if mail failed.
        }
    }

    /**
             *  @void
             *  Gets called if log exists.
             *  Modifies current log and adds the message to the log.
             *
             * @param string $log
             * @param DateTimeObject $date
             * @param string $message
             */
    private function edit($log, $date, $message)
    {
        $logcontent = "Time : " . $date->format('H:i:s')."\r\n" . $message ."\r\n\r\n";
        $logcontent = $logcontent . file_get_contents($log);
        file_put_contents($log, $logcontent);
    }

    private function getServerVars()
    {
        $output = "";

        $indicesServer = array('PHP_SELF','argv', 'argc', 'GATEWAY_INTERFACE',
        'SERVER_ADDR', 'SERVER_NAME', 'SERVER_SOFTWARE', 'SERVER_PROTOCOL',
        'REQUEST_METHOD', 'REQUEST_TIME', 'REQUEST_TIME_FLOAT', 'QUERY_STRING',
        'DOCUMENT_ROOT', 'HTTP_ACCEPT', 'HTTP_ACCEPT_CHARSET', 'HTTP_ACCEPT_ENCODING',
        'HTTP_ACCEPT_LANGUAGE', 'HTTP_CONNECTION', 'HTTP_HOST', 'HTTP_REFERER',
        'HTTP_USER_AGENT', 'HTTPS', 'REMOTE_ADDR', 'REMOTE_HOST', 'REMOTE_PORT',
        'REMOTE_USER', 'REDIRECT_REMOTE_USER', 'SCRIPT_FILENAME', 'SERVER_ADMIN',
        'SERVER_PORT', 'SERVER_SIGNATURE', 'PATH_TRANSLATED', 'SCRIPT_NAME',
        'REQUEST_URI', 'PHP_AUTH_DIGEST', 'PHP_AUTH_USER', 'PHP_AUTH_PW',
        'AUTH_TYPE', 'PATH_INFO', 'ORIG_PATH_INFO') ;

        $output .= '<table cellpadding="10">';
        foreach ($indicesServer as $arg) {
            if (isset($_SERVER[$arg])) {
                if (is_array($_SERVER[$arg])) {
                    // ignore subarrays for now...
                } else {
                    $output .= '<tr><td>'.$arg.'</td><td>' . $_SERVER[$arg] . '</td></tr>';
                }
            } else {
                $output .= '<tr><td>'.$arg.'</td><td>-</td></tr>';
            }
        }
        $output .= '</table>';
        return $output;
    }
}
