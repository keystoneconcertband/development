<?
	/*
		This class is the base KCB class. All top level functions should be included here
	*/	

	require("log.class.php");

	class KcbBase {
	    private $log;

		public function __construct() {
			// Show errors if dev environment
			$this->defaultSettings($this->isDevEnv());
			$this->log = new Log();

			if (session_status() == PHP_SESSION_NONE) {
				session_start();
			}
		}
				
		public function LogError($message) {
	        $this->log->write($message);			
		}
		
		public function sendEmail($toAddress, $message, $title, $html = true) {
			try {
				if($html) {
					// To send HTML mail, the Content-type header must be set
					$headers[] = 'MIME-Version: 1.0';
					$headers[] = 'Content-type: text/html; charset=iso-8859-1';					
				}
				
				// Additional headers
				$headers[] = 'From: KCB Website <web@keystoneconcertband.com>';
				$headers[] = 'Reply-To: web@keystoneconcertband.com';
				$headers[] = 'X-Mailer: PHP/' . phpversion();
						
				return mail($toAddress, $title, $message, implode("\r\n", $headers));	
			}
			catch(Exception $e) {
				$this->LogError($e->getMessage());
				return false;
			}
		}
		
		private function defaultSettings($showErrors) {
			if($showErrors) {
				ini_set('display_errors',1);
				ini_set('display_startup_errors',1);
				error_reporting(-1);
			}
			
			// Set timezone so that times are displayed correctly
			date_default_timezone_set('America/New_York');
			
			// Set admin email address errors should go to
			$admin_email = 'webmaster@keystoneconcertband.com';
		}
		
		private function isDevEnv() {
			if(strpos($_SERVER['SERVER_NAME'], 'dev') !== false || strpos($_SERVER['SERVER_NAME'], 'refresh') !== false) {
				return true;
			}
			else {
				return false;
			}
		}
	}
?>