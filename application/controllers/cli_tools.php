<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 * Example for creating CLI Tools that are useful from commandline
 *
 * @version     Daemon Services v1
 * @level1      controllers
 * 
 * @category    Controller
 * @author      Noah Spirakus
 * @Create      2011/11/10
 * @Modify      2013/01/01
 * @Project     DAEMONIZER
 * @link    php /BASE_DIR/cli.php tool_name (where)
 */

/**
 *  !!!!!!!!! THIS IS JUST AN EXAMPLE OF HOW WE USE IT !!!!!!!!!
 */

class tools extends CI_Controller
{
    
    function __construct(){
        
        parent::__construct();
        
        if( $this->input->is_cli_request() ){
            show_404(); // So they dont know this is an actual script
            die();
        }
        
        // === BEGIN ===

        $this->from_search_id = false;  // Sepcific to our functions below
        
    }
    
    function where(){
        

        echo "\n" . 'Where are you? Why your right here!' . "\n";
        
        echo "\n";
        echo '   ' . 'Enviroment:'      .chr(9). $this->show_env()  . "\n";
        echo '   ' . 'Codebase:'        .chr(9). BUILD  . "\n";
        
        echo "\n";
        echo '   ' . 'Server:' . "\n";
        echo '   ' . chr(9).'ID:'       .chr(9). SERVER_ID          . "\n";
        echo '   ' . chr(9).'Name:'     .chr(9). IDEN               . "\n";
            
        echo "\n";
        
        echo "Thanks for double checking!\n";
        echo "\n";
       
        
    }

    function search(){
        
        $this->load->model('domains_model');
        $this->load->model('domain_aliases_model');
        
        $s  =   $this->uri->segment(4);
        
        echo PHP_EOL.'Searching for "'.$s.'"...'.PHP_EOL;
        
        //Init domains array
        $domains = array();
        
        $records = $this->domain_aliases_model->find_all( array('s' => $s) );
        
        if( $records ){
            foreach($records as $record){
                if(!isset($domains[ $record->domain_id ])){
                    $domains[ $record->domain_id ]  =   $this->domains_model->get($record->domain_id, true);
                }
                $domains[ $record->domain_id ]->records[] = $record;
            }
        }
        
        if(count($domains)>1){
            
            foreach($domains as $domain){
                echo 'domain_id: ';
                if($record->domain_id<1000){
                    echo '0'.$record->domain_id.',';
                }else{
                    echo $record->domain_id.',';
                }
                echo chr(9);
                echo 'pool_id: '.   $domain->pool_id.','.chr(9);
                echo 'file_node: '. $domain->file_node.','.chr(9);
                echo 'dir: '.       $domain->directory.','.chr(9);
                
                if(count($domain->records) > 1 ){
                    echo array_pop($domain->records)->fqdn.', ....'.chr(9);
                }elseif(count($domain->records) ==1 ){
                    echo array_pop($domain->records)->fqdn.chr(9);
                }else{
                    echo 'No records'.chr(9);
                }
                
                echo PHP_EOL;
            }
        }else{
            echo 'Nothing was found';
        }

    }
    
    
    
    
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

}
