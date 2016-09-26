<?php

/**
 * Quick and dirty monitor for email-alerts when there are failed jobs in the Relay transcoding queue.
 * 
 * This script should be auto-run (cron) once a day (pref. morning).
 */
ini_set('mssql.charset', 'UTF-8');
//
$config = '/var/www/etc/techsmith-relay/relay_config.js';


//
echo(PHP_EOL."Relay CrashReporter Starting a new routine check.".PHP_EOL);
$reporter = new CrashReporter($config);
$reporter->log('I am running on server: ' . gethostname());
$reporter->log('My path is: ' . __DIR__);

$report = $reporter->check();

if($report === false){ $reporter->log('Something went wrong! See trace above - goodbye.'); exit(); }

if(sizeof($report) > 0){
	echo (PHP_EOL.PHP_EOL.'------ LIST OF FAILED JOBS ------'.PHP_EOL);
	echo (json_encode($report, JSON_PRETTY_PRINT));
	echo(PHP_EOL.'-------------- FIN --------------'.PHP_EOL);

}
exit();


class CrashReporter {

		private $config, $logline=1;

		function __construct($config_path) { $this->config = $this->getConfig($config_path); }

		public function check() {
			if(!$this->config){ return false; }
			$connection = $this->getConnection();
			if(!$connection){ return false; }

			// Run query
			$query = mssql_query("SELECT jobId, jobType, jobState, jobPresentation_PresId, jobQueuedTime, jobPercentComplete, jobFailureReason, jobNumberOfFailures, jobTitle FROM tblJob WHERE jobState = 3", $connection);
			// On error
			if($query === false) { $this->log('DB query failed (SQL).'); return false; }
			// Response
			$response = array();
			//
			$this->log("Number of failed jobs in queue: " . mssql_num_rows($query));

			// Loop rows and add to response array
			if(mssql_num_rows($query) > 0) {
				while($row = mssql_fetch_assoc($query)) {
					$response[] = $row;
				}
			}
			// Free the query result
			mssql_free_result($query);
			if($connection !== false) { mssql_close($connection); }
			//
			return $response;
		}

		private function getConnection() {
			$connection = mssql_connect($this->config['host'], $this->config['user'], $this->config['pass']);
			if(!$connection) { $this->log('DB connection failed (SQL).'); return false;}
			if(!mssql_select_db($this->config['db'])) { $this->log('DB table connection failed (SQL).'); return false; }
			return $connection;
		}

		private function getConfig($config_path) {
			$config = file_get_contents($config_path);
			if($config === false) {  $this->log('Config file not found!'); return false; }
			return json_decode($config, true);
		}

		public function log($msg) {
			$timestamp       = date('Y-m-d H:i:s');
			echo "[$timestamp] " . $this->logline++ . " :: $msg" . PHP_EOL;
		}
	}