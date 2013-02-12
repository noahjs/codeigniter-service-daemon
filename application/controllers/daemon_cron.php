<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * DAEMON_Controller Class -> extension of CodeIgniter Controller Class
 * 	   DAEMON_Controller located in application/core/daemon_Controller.php
 *
 * Used from commandline to process a queue as a Daemon
 *
 * @version     Daemon Services v1
 * @level1      controllers
 * 
 * @category    Controller
 * @author      Noah Spirakus
 * @Create      2011/11/10
 * @Modify      2013/02/13
 * @Project     DAEMONIZER
 * @link	php /BASE_DIR/cli.php daemon_name
 */
require APPPATH.'/core/DAEMON_Controller.php';

class daemon_cron extends DAEMON_Controller
{

    function __construct(){
		
		$config = array();
		$config['name'] 		= 'daemon_cron';	// <required> - Used as folder name and for logging, DO NOT HAVE SPACES
		$config['memory'] 		= '64M';			// <optional> - Amount of memory to allow script to have 
		$config['sql_timeout'] 	= '1200';			// <optional> - How long to manually set the sql SESSION wait_timeout for default: 1200 
		$config['verbose'] 		= false;			// <optional, default = false> - Will log to file AND output to screen, can be overridden with --verbose flag
		$config['models'] 		= array(			// Loads models requested
								    'accounts_model',
								    /* Whatever other models u need loaded */
								);
		$config['libraries'] 	= array('emailer');	// Loads libraries requested
		
		parent::__construct($config);	//Construct parent DAEMON_Controller
		
    }
    
    /*
     *	    EXAMPLE DAEMON
     *	    --------------
     *		Queue 1: Run continuously
     *		Queue 2: Run every 10 minutes
     *		Queue 3: Run every hour
     *
     **/
    
    function queue(){
		
		$this->logit('DAEMON', 'Service Started sucessfully w PID: '.getmypid());
		
		//Set reload timer
		$timer_3 = time();	// Remove old api-logs
		$timer_2 = time();	// Remove Admin logs
		$timer_1 = time();	// send some emails (Accounts created without domains )
		
		
		do{
		    
		    // Go through the various timers, and see if we should do something?

		    if(time() > $timer_3 ){ 
			
				if($this->verbose){
					$this->logit('daemon', 'RUNNING: Remove old api-logs' );
				}
			
				$this->worker_cleanapilogs();
				
				$timer_3 = time() + 3600; 	//Every Hour
			
		    }elseif(time() > $timer_2){
			
				if($this->verbose){
				    $this->logit('daemon', 'RUNNING: Clean authentication logs' );
				}
				
				$this->worker_cleanauth_logs();
				
				$timer_2 = time() + 3600; 	//Every Hour
			
		    }elseif(time() > $timer_1){
			
				if($this->verbose){
					$this->logit('daemon', 'RUNNING: send some emails' );
				}
				
				$this->worker_sendemails();
				
				$timer_1 = time() + 3600 * 6; 	//Every 6 Hours
			
		    }else{
				
				//if no queued items AND not running something with timer sleep so processor does not run away
				sleep(30);

				// Make sure SQL doesnt time out, this is a graceful reconnect, notice it is AFTER the sleep
				$this->db->reconnect();
			
		    }
		    
		    
		}while($this->as_service AND $this->continue_loop());
		
    }
    
    
    /************************************************/
    /**** Setup your processing functions below *****/
    /************************************************/
    
    
    function worker_cleanapilogs(){
		
		//Keep GET requests for 14 days all others for 90 days
		
		//Delete GET logs after 14 days
		$this->db->where('method', 'GET');
		$this->db->where('time <', (time()-(86400*14)) ); 	//Before 14 days ago
		$this->db->delete('api_logs');
		
		
		//Delete all logs after 90 days
		$this->db->where('time <', (time()-(86400*90)) ); 	//Before 90 days ago
		$this->db->delete('api_logs');
		
    }
    
    
    function worker_cleanauth_logs(){
		
		//Keep successful login for 30 days failed for 90 days
		
		//Delete successful logs after 30 days
		$this->db->where('result', 'succeed');
		$this->db->where('date_added <', (time()-(86400*30)) ); 	//Before 30 days ago
		$this->db->delete('authentication_logs');
		
		
		//Delete all logs after 90 days
		$this->db->where('date_added <', (time()-(86400*90)) ); 	//Before 90 days ago
		$this->db->delete('authentication_logs');
	
    }


    function worker_sendemails(){

		// Send some followup emails to accounts about i dont know how they are awesome a week after the signed up!
	
    }
    
    
    
}