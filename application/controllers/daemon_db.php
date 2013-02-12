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

class daemon_db extends DAEMON_Controller
{

    function __construct(){
		
		$config = array();
		$config['name'] 		= 'daemon_db';		// <required> - Used as folder name and for logging, DO NOT HAVE SPACES
		$config['memory'] 		= '64M';			// <optional> - Amount of memory to allow script to have 
		$config['sql_timeout'] 	= '2400';			// <optional> - How long to manually set the sql SESSION wait_timeout for default: 1200 
		$config['verbose'] 		= false;			// <optional, default = false> - Will log to file AND output to screen, can be overridden with --verbose flag
		$config['models'] 		= array(			// Loads models requested
								    'accounts_model',
								    /* Whatever other models u need loaded */
								);
		$config['libraries'] 	= array(
									'billing_library',
								);		// Loads libraries requested
		
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
		
		//$queue_timer_1 = time() + 600; 	//Every 10 min
		//$queue_timer_2 = time() + 3600; 	//Every 1 hour
		
		do{
		    
		    // Get list of things_to_do (20)
		    if( $things_to_do = $this->table_with_stuff_todo_done_model->get_queue() ){		//Get all (100) items in the queue
			
				// Go through events
				foreach( $things_to_do as $to_do){
				    
				    $this->logit('DAEMON', 'Processing Thing to do '.$to_do->id.' for Account id : '.$to_do->account_id);
				    
				    if( $this->worker_1($to_do) ){

				    	// Update complete
				    	$this->table_with_stuff_todo_done_model->update_status( $to_do, 'complete' );

				    	//Log
				    	$this->logit('DAEMON', 'INFO: Thing to do '.$to_do->id.' Completed');

					}else{

				    	// Update complete
				    	$this->table_with_stuff_todo_done_model->update_status( $to_do, 'failed' );

				    	//Log
				    	$this->logit('DAEMON', 'ERROR: Thing to do '.$to_do->id.' Failed');

					}
					
				}
			
		    }else{
				
				//if no queued items AND not running something with timer sleep so processor does not run away
				sleep(10);
				
		    }
		    
		    
		}while($this->as_service AND $this->continue_loop());
		
    }
    
    
    /************************************************/
    /**** Setup your processing functions below *****/
    /************************************************/
    
    
    function worker_1($to_do){
		
		
    	// Do whatever you need to do with this todo here

    	// Maybe these todos are invoices that need to be ran?

    	if( $this->billing_library->run_invoice( $to_do ) ){
    	
    		return true;
    	
    	}else{
    		
    		return false;

    	}
	
    }
    
    
}