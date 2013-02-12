#!/usr/bin/php -c /data/p01/www/conf/php_service.ini
<?php
/**
 * This script will be used to start, stop, restart and get the status of all of your daemons
 * 
 * @author	- Noah Spirakus
 * 			- www.noahjs.com
 * 			- twitter.com/noahjs
 * 
 * INSTALLATION
 *
 * 	.......
 * 
 * USAGE
 * 
 * To start your daemon:			/dir/where/saved/brains {SCRIPT} start
 * To stop your daemon:				/dir/where/saved/brains {SCRIPT} stop
 * To restart your daemon:			/dir/where/saved/brains {SCRIPT} restart
 * To get your daemon's status:		/dir/where/saved/brains {SCRIPT} status
 * 	Additional Options:
 * 			--verbose	Tells subsequent script to run in verbose mode
 * 			--force		When used with restart or stop, instead of "kill" command a "kill -9" command is run
 * 	NOTE:
 * 		- Instead of a script name you can use a group name to refer to multiple scripts like ALL or GROUP1
 */

// * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *


//List all potential scripts that can be run
	$avail_scripts	=	array(	
								'daemon_cron',
								'daemon_db',
				      		);

//List groups and define scripts in each
	$group_scripts	=	array(
							'all'		=> 	$avail_scripts,
							'daemons'	=> 	array( 'daemon_cron', 'daemon_db' ),

							/* 
								//EXAMPLES BELOW
							'web'		=> 	array( 'web_worker' ),
							'storage'	=> 	array( 'backup', 'cleanup' ),
							'cron'		=> 	array( 'billing', 'daemon_cron', 'daemon_db' ),
							*/
						);
	
	
//All available commands that can be ran
	$commands	=	array('start', 'stop', 'restart', 'status');


// don't touch anything belove this line, everything runs alright now
// * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *

// Set/include enviroment Configs
	
	define('SERVER_LOG', '/var/log/your_project');	// Where do the logs go
	define('SERVER_DIR', '/var/www/your_project');	// Where is this file located
	

	include('/somewhere/outside/webroot/iden.php');					//Defines the enviroment AND the box, Sits OUTSIDE svn/git repo so it doesnt get overwritten
	
	/**
	 * 	Identity file iden.php includes:
	 *
	 *		- If not including iden.php use below lines to define constants
	 * 		
	 * 		define(	'IDEN', 		'web8'	);
	 * 		define(	'SERVER_ID', 	'27'	);
	 * 
	 */
	
	include('../conf/conf.php');		// Used to make sure user knows the enviroment they are in
	
	/**
	 * 	Config file conf.php includes:
	 *
	 *		- If not including conf.php use below lines to define constants
	 * 		
	 * 		define(	'ENVIRONMENT', 	'production'  );
	 * 
	 */


//Used for testing Hi ==> Hello
if(isset($argv[1]) AND ($argv[1] == 'hello')){
	die('Hi!'. "\n");
}

// TOOLS

	// Used for searching through domains
	if(isset($argv[1]) AND ($argv[1] == 'search')){
		passthru('/usr/bin/php -c /data/p01/www/conf/php_service.ini '.SERVER_DIR.'/cli.php search ' .$argv[2]);
		die('');
	}

	//Used for finiding which box you are on
	if(isset($argv[1]) AND ($argv[1] == 'where')){
		
		passthru('/usr/bin/php -c /data/p01/www/conf/php_service.ini '.SERVER_DIR.'/cli.php where');
		die('');

	}

// END Tools


//1. Check script input
	
	if(in_array('--help',$argv)){
		
		echo "\n" . 'Usage '.show_env().':  ./brains  {Script or Group}  <Commands>  [options]' . "\n";
		
		echo print_column('', 3) . print_column('Scripts:', 12)  . print_column(implode(', ',$avail_scripts), 20) 		. "\n";
		echo print_column('', 3) . print_column('Groups:', 12)   . print_column(implode(', ',array_keys($group_scripts)), 20) 	. "\n";
		echo print_column('', 3) . print_column('Commands:', 12) . print_column(implode(', ',$commands), 20) 			. "\n";
		
		echo "\n";
		
		die();
	}
	
	if(in_array('--force',$argv)){
		$stop_command	=	'kill -9 ';
	}else{
		$stop_command	=	'kill ';
	}
	
	if(in_array('--verbose',$argv)){
		$extracommands	=	' --verbose ';
	}else{
		$extracommands	=	' ';
	}
	
	
	//Validate incoming argv
		
		
		// if invalid SCRIPTNAME or GROUPNAME, die and display script syntax
		if ( !isset($argv[1]) OR ( !in_array($argv[1], $avail_scripts) AND !array_key_exists($argv[1], $group_scripts) ) )
			die('Available Scripts '.show_env().':  all|'.implode('|',$avail_scripts).' ' . "\n");
		
		// if invalid "COMMAND" argument, die and display command syntax
		if ( !isset($argv[2]) OR !in_array($argv[2], $commands) )
			die('Command Syntax '.show_env().':  '.implode('|',$commands).' ' . "\n");
		

		// !!!!!!!!! Up to you if you Want people to be able to start ALL daemon on a single box !!!!!!!!!
		// We dont but depends on your system

		// Dont allow all scripts to be started up at once
		/*
		if( ($argv[1] == 'all') AND ($argv[2] == 'start') )
			die('Invalid Syntax: Start All is not allowed'. "\n");
		}
		*/
		
	
	
	//All seems to be in order, setup variables that refer to requested action
		
		if(array_key_exists($argv[1], $group_scripts)){
			define('HEADERS', true);
			$scripts		=	$group_scripts[ $argv[1] ];	// Used when loading scripts for defined "group" like CRON1
		}else{
			define('HEADERS', false);
			$scripts[]		=	$argv[1];			// loads selected script
		}
		
		$action		=	$argv[2];	//Load requested action
		
	
	
//2. Run command

	// treat the requested action
	switch($action) {
		
		// START a process
		case 'start':
			
			echo print_column('BRAINS  in '.show_env().'', 20) .  "\n";
			
			if(HEADERS){
				echo print_column("Script", 20, true) 	. print_column('Status / Action', 20, true) 	. print_column('PID', 20, true) .  "\n";
			}
			
			foreach($scripts as $script){
				
				// die if the server is already started
				if ($pid = is_service_running($script)){
					echo print_column($script, 20) 		. print_column('already running', 20)  		.  print_column($pid, 20)  	. "\n";
				}else{
					
					start_daemon($script, $extracommands);
					
					echo print_column($script, 20) 		. print_column('started', 20)  			.  print_column('', 20)  	. "\n";
					
					//Sleep to prevent lock on mkdir
					if(count($scripts) > 1){
						echo print_column('', 20) . print_column('...sleeping for 2...', 20) . "\n";
						sleep(1.5);
					}
					
					
				}
				
				
			}
			break;
		
		
		// STOP a process
		case 'stop':
			
			echo print_column('BRAINS  in '.show_env().'', 20) .  "\n";
			
			if(HEADERS){
				echo print_column("Script", 20, true) 		. print_column('Status / Action', 20, true) 	.  "\n";
			}
			
			foreach($scripts as $script){
				
				// check if process is currently running
				if ($pid = is_service_running($script)){
					
					echo print_column($script, 20) 		. print_column('Stopping', 20)			. "\n";
					
					stop_daemon($pid, $script, $stop_command);
					
					echo print_column($script, 20) 		. print_column('now stopped', 20)		. "\n";
					
				}else{
					echo print_column($script, 20) 		. print_column('already stopped', 20)		. "\n";
				}
				
				
			}
			
			break;
		
		
		// RESTART a process
		case 'restart':
			
			echo print_column('BRAINS  in '.show_env().'', 20) .  "\n";
			
			if(HEADERS){
				echo print_column("Script", 20, true) 		. print_column('Status / Action', 20, true) 	.  "\n";
			}
			
			foreach($scripts as $script){
				
				$was_runing = false;
				
				// die if the server is already started
				if ($pid = is_service_running($script)){
					
					echo print_column($script, 20) 		. print_column('Stopping', 20)	  		. "\n";
					
					stop_daemon($pid, $script, $stop_command);
					$was_runing = true;
					
					echo print_column($script, 20) 		. print_column('now stopped', 20)  		. "\n";
					
				}
				
				if(count($scripts) > 1){
					
					if($was_runing){
						start_daemon($script, $extracommands);
						echo print_column($script, 20) 	. print_column('restarted', 20) 		. "\n";
					}else{
						echo print_column($script, 20) 	. print_column('stopped', 20) 			. "\n";
					}
					
				}else{
					start_daemon($script, $extracommands);
					echo print_column($script, 20) 		. print_column('started', 20) 			. "\n";
				}
				
			}
			
			break;
		
		
		// STATUS of a process
		case 'status':
			
			echo print_column('BRAINS  in '.show_env().'', 20) .  "\n";
			
			if(HEADERS){
				echo print_column("Script", 20, true) 		. print_column('Status', 20, true) 		. print_column('PID', 10, true) 	. "\n";
			}
			
			foreach($scripts as $script){
				
				if ($pid = is_service_running($script)){
					echo print_column($script, 20) 		. print_column('running', 20) 			. print_column('PID: '.$pid, 10) 	. "\n";
				}else{
					echo print_column($script, 20) 		. print_column('stopped', 20) 			. print_column('0', 10) 		. "\n";
				}
				
			}
			
			break;
		
		
		default:
			die('Syntax '.show_env().': ' . __FILE__ . ' start|stop|kill|restart|status' . "\n");
		
	}


// ===================== Helper Functions =====================


// Print nice looking column
function print_column($text, $length, $header = false){
	
	$strlen = strlen($text);
	
	while($strlen < $length){
		$text = $text . ' ';
		$strlen++;
	}
	
	if($header){
		return "\033[0;30m\033[47m"   .   $text  .  "\033[0m";	//Displays highlighted text in bash
	}else{
		return $text;
	}
	
}

// Check if the Service is running
function is_service_running($service){
	
	//get PID
	$pidfile = SERVER_LOG.'/'.$service.'/'.IDEN.'_pid';
	
	if(!file_exists($pidfile)) return false;
	
	$pid = trim(@file_get_contents($pidfile));
	
	// create our system command
	$cmd = "ps ".$pid;
	
	// run the system command and assign output to a variable ($output)
	exec($cmd, $output, $result);
	
	// check the number of lines that were returned
	if(count($output) >= 2){
		// the process is still alive and running
		return $pid;
	}else{
		// the process is dead
		return false;
	}
	
}

// SHow highlighted version of ENVIRONMENT variable
function show_env(){
	
	$text = "\033[0;30m\033[" ;
	
	switch(ENVIRONMENT){
		case 'production':
			$text .= "42m";
			break;
		case 'staging':
			$text .= "43m";
			break;
		case 'development':
			$text .= "41m";
			break;
		default:
			$text .= "47m";
			break;
	}
	
	$text .= '['.strtoupper(ENVIRONMENT).']';
	
	$text .= "\033[0m";
	
	return   $text;
}

function start_daemon($service, $extracommands){
	
	if( !is_dir( SERVER_LOG.'/'.$service ) ){
		mkdir( SERVER_LOG.'/'.$service, 0755, true );
	}
	
	$exec_string = '/usr/bin/php -c '.SERVER_DIR.'/conf/php_service.ini '.SERVER_DIR.'/cli.php '.$service. ' ' .$extracommands.' >> '.SERVER_LOG.'/'.$service. '/stdout_'.IDEN.'.log & ';
			/*
				Ex. /usr/bin/php /SERVER_DIR/cli.php daemon_name >> /SERVER_LOG/daemon_name/stdout_web1.log &
				
				1. /usr/bin/php								==> Where PHP executable is stored
				2. -c /SERVER_DIR/conf/php_service.ini		==> Override to use specific ini file (assuming u have another less liberal used for Apache)
				3. /var/www/html/domain_folder/cli.php		==> Path to script to be executed
				4. daemon_name									==> Arguments passed to script
				5. >> SERVER_LOG/daemon_name/stdout_web1.log	==> Force all script output into a stdout log file
				6. &											==> Prevents script from outputting to console from background
			*/
	exec($exec_string);
	
}

function stop_daemon($pid, $script, $stop_command = ''){
	
	//used if restarting all to make sure only ones that were runnning are restarted
	$was_runing = true;
	
	echo print_column(" - ", 20) 		. print_column('Stopping', 20)		. "\n";
	
	exec($stop_command . $pid);
	
	// See if script is still running
	$timer = time();
	do{
		//If script doesnt stop after 60 seconds kill -9 the script
		if((time() - $timer) > 60){
			
			echo print_column($script, 20) 		. print_column('stalled now killing', 20)		. "\n";
			
			exec('kill -9 ' . $pid);
			$reloop = false;

		}elseif(is_service_running($script)){ // Still running, check again
			$reloop = true;
			sleep(1);
		}else{
			$reloop = false;
		}
	}while($reloop);
	
	
	// where is pid file
	$pidfile = SERVER_LOG.'/'.$service.'/'.IDEN.'_pid';
	
	//Remove PID file
	exec('rm -f '.$pidfile);
	
}

