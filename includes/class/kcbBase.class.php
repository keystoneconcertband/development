<?
	/*
		This class is the base KCB class. All top level functions should be included here
	*/	
	class kcbBase {
		public function __construct() {
			// Show errors if dev environment
			$this->defaultSettings($this->isDevEnv());

			if (session_status() == PHP_SESSION_NONE) {
				session_start();
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