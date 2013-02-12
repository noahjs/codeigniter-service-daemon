<?php 

/**
 *
 * CLI.php is called from command line to start the various application service daemons
 *
 * @version     services v1
 * @level1      cli.php  <script name>
 * 
 * @category    PHP Execution File
 * @author      Noah Spirakus
 * @Create      2011/11/11
 * @Modify      2013/02/13
 * @Project     ---
 * @link		php cli.php
 */
	
	//Allows for interception of POSIX commands like kill
		declare(ticks = 1);
	

	/* make sure this isn't being called by a web browser */
		if (isset($_SERVER['REMOTE_ADDR'])) die('Permission denied.');
	
	//Minimum settings used by cron
		set_time_limit(0);
		ini_set('memory_limit', '256M');


	//set some constants
		global $servicepleasestop;	//Used by POSIX commands
		$servicepleasestop = false;	//Used by POSIX commands
		
		define('CMD', 1);
		$_SERVER['DOCUMENT_ROOT'] = __DIR__;
	
	$_GET = '';
	
	if(!isset($_SERVER['argv'][1])){
		
		die('Incorrect Arguments.');
	
	
	// ============   DAEMONS   ============
	
	
	}elseif($_SERVER['argv'][1]	==	"daemon_cron"){			// Does some things for overall system
		
		$_SERVER['PATH_INFO'] 		=	'daemon_cron/queue';
		$_SERVER['REQUEST_URI'] 	=	'daemon_cron/queue';
		$_SERVER['QUERY_STRING']	=	'daemon_cron/queue';
		
	}elseif($_SERVER['argv'][1]	==	"daemon_db"){			// Does something based on records in DB
		
		$_SERVER['PATH_INFO'] 		=	'daemon_db/queue';
		$_SERVER['REQUEST_URI'] 	=	'daemon_db/queue';
		$_SERVER['QUERY_STRING']	=	'daemon_db/queue';
		
	
	
	// ============   CLI Tools   ============
	
	
	}elseif($_SERVER['argv'][1]	==	"search"){			// Used for command line searching for domains
		
		$_SERVER['PATH_INFO'] 		=	'cli_tools/search/'.$_SERVER['argv'][2];
		$_SERVER['REQUEST_URI'] 	=	'cli_tools/search/'.$_SERVER['argv'][2];
		$_SERVER['QUERY_STRING']	=	'cli_tools/search/'.$_SERVER['argv'][2];
		
	}elseif($_SERVER['argv'][1]	==	"where"){			// Used to verify where current server is.
		
		$_SERVER['PATH_INFO'] 		=	'cli_tools/where';
		$_SERVER['REQUEST_URI'] 	=	'cli_tools/where';
		$_SERVER['QUERY_STRING']	=	'cli_tools/where';
		
	}else{
		die('Unknown Service Requested.');
	}
	
	
	//Attach script to listen to POSIX (kill) commands
	if(function_exists('pcntl_signal')){
		pcntl_signal(SIGTERM, 'sig_handler');
		pcntl_signal(SIGINT,  'sig_handler');
	}else{
		//echo 'WARNING: No PCNTL module Installed.'.chr(10);
	}
	
	
	//Signal handler function 
	function sig_handler($signo)
	{
		switch ($signo) {
			case SIGTERM:
			case SIGINT:
				global $servicepleasestop;
				$servicepleasestop = true;
				break;
			default:
				echo 'Unknown Signal from POSIX ['.$signno.']';
		}
	}
	
	
	
	//Off we go into the framework
	include('www/index.php');
	
	
	

/* End of file cli.php */
/* Location: ./cli.php */