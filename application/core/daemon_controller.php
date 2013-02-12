<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * DAEMON_Controller Class -> extension of CodeIgniter Controller Class
 * 	DAEMON_Controller used by services to extend logging, security, and prolonged databse access to concurrent databases
 *
 * @version     SERVICE Services v1
 * @level1      core
 * @level2    	n/a
 * @level3    	n/a
 * 
 * @category    core (extends native CI_Controller)
 * @author      Noah Spirakus
 * @Create      2011/07/12
 * @Modify      2011/07/12
 * @Project     MobileMarket.it
 * @link	n/a
 */
class DAEMON_Controller extends CI_Controller {
	
	// Constructor function
	function __construct($config)
	{
		
		// Run construct for CI Controller
		parent::__construct();
		
		// Setup everything
		$this->init($config);
		
		// we WANT errors for commandline tools just not API etc
		error_reporting(E_ALL);
		ini_set('display_errors', 1);

		// Do you want to keep track of daemons in DB, ie. if they failed?
		define('LOG_IN_DB', false);

		// In your controller you must define:
		/*
			define('BUILD',  'v4.3-1234');			// Build / version of the code running so you can keep track in the logs
			define('ENVIRONMENT', 'production');  	// on specific logic is based off "production", but we also use development, and staging
			define('IDEN',  'web1');				// human readable name of the server
			define('SERVER_ID',  '1234'); 			// Can be the ID of server if you keep track or could be same as IDEN
			define('SERVICE_NAME',  'daemon_name');	// Which script is this? billing, cleanup etc..
			define('SERVER_LOG', '/var/log/ci/');	// Where do u want to keep all your logs, they will be written into subfolder based off SERVICE_NAME
		*/
	}
	
	
	// Our startup function
	private function init($config){
		
		
		$this->service_name	=	$config['name'];
		define('SERVICE_NAME', $this->service_name);
		
		//Set to true to run as system service
		if(defined('CMD') AND (CMD == 1) ){
			
			// Yes we are running "as a service"
			$this->as_service = true;
			
			// How often we log to DB
			$this->last_db_log = 0;
			
			//Check if script should run in verbose mode
			$this->verbose	=	(isset($config['verbose']))?$config['verbose']:false;
			
			//Check if script ran from command line with forced verbose mode
			// This will echo to stdout AS WELL as the log files
			if(is_array($_SERVER['argv']) AND in_array('--verbose', $_SERVER['argv']) ) $this->verbose = true;
			
			//Show service has started in STDOUT
			echo '['.gmdate('Y-m-d H:i:s').'] '.SERVICE_NAME.' >> Service Started in '.ENVIRONMENT.' w/ PID: '.getmypid()."\n";
			
			//Startup logging, create dirs etc..
			$this->logit_init(true);
			
		}else{
			
			//If ran from web browser ONLY ALLOW IN NON PRODUCTION
			if(ENVIRONMENT == 'production'){
				$this->logit('SERVICE', 'WARNING: Access DENIED for IP Address: '.$_SERVER['REMOTE_ADDR'].' in PRODUCTION');
				die('Not allowed in PRODUCTION');
			}

			// No we are not on command line
			$this->as_service = false;
			
			//Yes echo to screen
			$this->verbose	  = true;
			
			//Alert that service started and show process ID
			echo SERVICE_NAME.' Service Started in '.ENVIRONMENT.' w/ PID: '.getmypid();
			
			// Alert that this is only allowed in NON-Production
			$this->logit('SERVICE', 'WARNING: WEB Access granted in ['.ENVIRONMENT.'] enviroment for IP Address: '.$_SERVER['REMOTE_ADDR'].', access will be denied in PRODUCTION');

		}
		
		$this->logit('SERVICE', '-------------------------------------------------');
		$this->logit('SERVICE', 'Running Code: '.BUILD);
		$this->logit('SERVICE', 'Running in the '.strtoupper(ENVIRONMENT).' environment');
		$this->logit('SERVICE', '-------------------------------------------------');
		
		
		// SETUP Enviroment for SERVICE type process
			
			set_time_limit(0);
			ini_set('memory_limit', ( isset($config['memory']) ? $config['memory'] : '256M' ));
			$this->debug	=	(isset($config['debug']))?$config['debug']:false;
			
		
		
		// SETUP MODELS AND DATABASE
			
			if(isset($config['models']) AND count($config['models']) > 0){
				foreach($config['models'] as $mod){
					
					//Load each model
					$this->load->model($mod);
					
					// Should we call the Init method on the models?
					if($this->as_service){
						
						/*
						
						//Get the 'callable' model name, ie. not including directory / path
						$mod_name	=	substr($mod, (strrpos($mod, '/', -1) + 1), strlen($mod));
						
						//Check if init method exists for the model, call it
						if(method_exists($this->$mod_name, 'service_init')){
							
							//Init method on Modal sets high timeout on alternate databases
							$this->$mod_name->service_init();
							
						}
						
						*/
						
					}
				}
			}
			
			// We only persist the DB connection if its a service daemon
			if($this->as_service){
				
				// How long do we want the DB connection to persist, depends how long you might go between queries
				$timeout  =  (isset($config['sql_timeout']) AND $config['sql_timeout'] AND is_numeric($config['sql_timeout']) )?$config['sql_timeout']:1200;
				
				//Set primary DB timeout
				$this->db->query("SET SESSION wait_timeout = ".intval($timeout));
				$this->db->save_queries = false;
			}
		
		
		// SETUP LIBRARIES that were requested
		
			if(isset($config['libraries']) AND count($config['libraries']) > 0){
				foreach($config['libraries'] as $lib){
					$this->load->library($lib);
				}
			}
		
	}
	
	//Used to continue SERVICE loop do{ ....}while( $this->continue_loop() );
	public function continue_loop(){
		
		global $servicepleasestop;
		
		/*

		// This will allow you to stop a Daemon by writing a _STOP file in the directory
		$stopfile = SERVER_LOG.'/'.SERVICE_NAME.'/'.IDEN.'_STOP';
		
		if(file_exists($stopfile)){
			$this->logit('PROC', 'Shutting Down from file check...');
			if($this->as_service){
				echo '['.gmdate('Y-m-d H:i:s').'] '.SERVICE_NAME.' >> PID: '.getmypid().' Shutting Down from file check.'."\n";
			}
			return false;
		}else
		*/

		if(isset($servicepleasestop) AND $servicepleasestop){
			
			$this->logit('PROC', 'Shutting Down from POSIX command...');
			if($this->as_service){
				echo '['.gmdate('Y-m-d H:i:s').'] '.SERVICE_NAME.' >> PID: '.getmypid().' Shutting Down from POSIX command.'."\n";
			}
			
			if( LOG_IN_DB ){
				
				// Log to DB
				$this->db->where('server_id', SERVER_ID);
				$this->db->where('service', SERVICE_NAME);
				$this->db->update('service_daemons', array(
										'status'       => 0,
										'date_touch'   => time(),
										'date_stopped' => time()
									   ));
				
			}
			
			return false;

		}else{
			
			// If running as SERVICE 
			if($this->as_service){
			    
				// touch a status file
				if(!touch(SERVER_LOG.'/'.SERVICE_NAME.'/'.IDEN.'_status')){
					echo '['.gmdate('Y-m-d H:i:s').'] '.SERVICE_NAME.' >> Cant touch status file!!!!!!'."\n";
				}
				$this->logit_init();	//Check init info needs to be cahnged (like date folder)
				
				
				// Log to DB every 5 min, we dont want to flood replication
				if( LOG_IN_DB AND ( $this->last_db_log < ( time() - (60 * 5) ) ) ){
					
					// Log to DB that script is still running?
					
					$this->db->where('server_id', SERVER_ID);
					$this->db->where('service', SERVICE_NAME);
					$this->db->update('service_daemons', array('status' => 2, 'date_touch' => time()) );
						
				}
				
			}
			
			return true;
		}
		
		// track memmory usage in NON Production
		if(ENVIRONMENT != 'production'){
		    $this->logit('DEBUG', 'Mem usage:'.memory_get_usage(true));
		}
		
		
	}


    // Used to setup folders etc for logging
	public function logit_init($init = false){
		
		//Run if first time script started OR if current day is not the day we have saved in $this->day
		if($init OR ($this->day != gmdate('Ymd')) ){
			
			//CREATE The folder recursivelys
			$this->day	=	gmdate('Ymd');
			$dir = SERVER_LOG.'/'.SERVICE_NAME.'/logs/'. $this->day.'/';
			if(!is_dir($dir)){
				mkdir($dir, 0777, true );
				
				//prevents locks before link is executed
				sleep(1);
				
				$wasdir = false;
			}else{
				$wasdir = true;
			}
			
			//Create the Log file
			if(isset($this->logfile)){fclose($this->logfile);}
			$this->logfile = fopen($dir.IDEN.'_log.txt', 'a');
			
			
			if(!$wasdir){
				//log that new folder wsa created
				$this->logit('SERVICE', 'New Folder Created.('.$dir.')');
				
				//Remove old current link if exists
				if( file_exists( SERVER_LOG.'/'.SERVICE_NAME.'/logs/current' ) ){
					$link_command	=	'unlink '.SERVER_LOG.'/'.SERVICE_NAME.'/logs/current';
					exec($link_command, $link_output, $link_result);
				}
				
				
				//Link CURRENT folder to most recent folder
				$link_command	=	'ln -s '.SERVER_LOG.'/'.SERVICE_NAME.'/logs/'. $this->day.'/'.' '.SERVER_LOG.'/'.SERVICE_NAME.'/logs/current';
				exec($link_command, $link_output, $link_result);
				
				
				$this->logit('SERVICE', '+++++++++++++++++++++++++++++++++++++++++++++++++');
				
				//Log result of the link if directory was created, ie.was not a dir before
				if($link_output){
					$this->logit('SERVICE', 'Relinked to current directory ('.$dir.')');
				}else{
					$this->logit('SERVICE', 'FAILED to relink to current directory ('.$dir.')');
				}
				
				$this->logit('SERVICE', 'Code: '.BUILD.' in '.strtoupper(ENVIRONMENT).' ENV');
				$this->logit('SERVICE', '+++++++++++++++++++++++++++++++++++++++++++++++++');
				
			}
			
		}
		
		// This only runs if its the FIRST TIME not every day
		if($init){
			
			//Write pid file ONLY RAN ON First loop execution
			$pidfile = fopen(SERVER_LOG.'/'.SERVICE_NAME.'/'.IDEN.'_pid', 'w');
			fwrite($pidfile, getmypid());
			fclose($pidfile);


			// If you want to Log to DB
			if( LOG_IN_DB ) {

				$this->db->where('server_id', SERVER_ID);
				$this->db->where('service', SERVICE_NAME);
				$this->db->from('service_daemons');
				$q	=	$this->db->get();
				if($q->num_rows() > 1 ){ // Hmm we have a few rows, thats not right
					
					// Delete ALL old
					$this->db->where('server_id', SERVER_ID);
					$this->db->where('service', SERVICE_NAME);
					$this->db->delete(array('service_daemons'));

					// Insert
					$args = array(
									'server_id'    => SERVER_ID,
									'server_name'  => IDEN,
									'service'      => SERVICE_NAME,
									'status'       => 1,
									'date_touch'   => time(),
									'date_started' => time(),
									'date_stopped' => 0
								);
					
					// Create
					$this->db->insert('service_daemons', $args);
					
				}elseif($q->num_rows() == 1 ){ // we already have a row just update it

					$args = array(
									'status'       => 1,
									'date_touch'   => time(),
									'date_started' => time(),
									'date_stopped' => 0
								);
					
					// Update 
					$this->db->where('server_id', SERVER_ID);
					$this->db->where('service', SERVICE_NAME);
					$this->db->update('service_daemons', $args)
					
				}else{	// We dont have a row yet

					$args = array(
									'server_id'    => SERVER_ID,
									'server_name'  => IDEN,
									'service'      => SERVICE_NAME,
									'status'       => 1,
									'date_touch'   => time(),
									'date_started' => time(),
									'date_stopped' => 0
								);
					
					// Create
					$this->db->insert('service_daemons', $args);
					
				}
			}
			
		}
		
	}
	
	//Called whenever something needs to be logged
	public function logit($who, $string){
		
		if($this->as_service){
			// Log to file
			fwrite($this->logfile, '['.gmdate('Y-m-d H:i:s').'] '.$who.' >> '.$string."\n");
			
			// If verbose throw to STDOUT too
			if($this->verbose){
				echo '['.gmdate('H:i:s').'] '.$who.' >> '.$string.chr(10);
			}
		}else{
			// Web browser
			echo $who.' >> '.$string.'<br>';
		}
		
	}
	
}