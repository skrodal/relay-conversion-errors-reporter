_This service is tailor-made for UNINETT AS for a specific use-case. Its re-usability may, as such, be limited._

This tiny service detects and alerts (e.g. via CRON mail) of any active conversion jobs in the Relay queue that have failed (crashed). 

## About

From time to time, conversion jobs on our Relay Severs crash for reasons unknown (Relay's own logfiles are very vague on the subject). 
Relay will frequently retry these jobs (and fail), stealing valuable processing time from other healthy recordings in the queue. If 
crashed jobs are not manually dealt with, the number of crashed jobs will soon consume all resources from the workers. Over time, a 
crashed job that has not been manually dealt with, will be retried 1000s of times... 

With the reporter running, a Relay administrator can get daily updates of the queues 'health' and act accordingly.

## Configure
```json

{
	"host"					:	"",
	"db"					  :	"",
	"user"					:	"",
	"pass"					:	""
}
```

### CRON-job

Example (run at 07:00 every morning): 

```sh
# Relay check for crashed recordings in queue
	*      7                       *       *       *       php /path/to/script/index.php | mail -s "Relay Crashmonitor - Daily Report" "relay.administrator@uninett.no"
```

### Sample email report

```sh
Relay CrashReporter Starting a new routine check.
[2016-09-23 07:33:01] 1 :: I am running on server: servername
[2016-09-23 07:33:01] 2 :: My path is: /path/to/script/
[2016-09-23 07:33:01] 3 :: Number of failed jobs in queue: 1


------ LIST OF FAILED JOBS ------
[
   {
       "jobId": 253308,
       "jobType": 0,
       "jobState": 3,
       "jobPresentation_PresId": 34385,
       "jobQueuedTime": "Sep 23 2016 07:18:25:967AM",
       "jobPercentComplete": 3,
       "jobFailureReason": "An error occurred in the encoder",
       "jobNumberOfFailures": 122,
       "jobTitle": null
   }
]
-------------- FIN --------------
```