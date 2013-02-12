<?php 

/**
 *
 * Site Config Class, used for config variables that change per host
 * 	- DO NOT OVERWRITE FILE when delopying using SVN or git, this file defines the box it is running in
 *
 * ++++++++ DEVELOPERS SHOULD ONLY EDIT LOCAL COPY OF THIS FILE +++++++
 *
 * @version     site_conf v1
 * @level1      ./
 * 
 * @category    Controller
 * @author      Noah Spirakus
 * @Create      2011/11/11
 * @Modify      2011/11/11
 * @Project     ---
 * @link	required from  ./www/index.php
 * 			- require_once '/location/on/server/iden.php';
 */

	

// ===========================================================================================================
// ===========================================================================================================
//
//  		This file should be kept OUTSIDE VERSION CONTROL because its different on every box 
//
// ===========================================================================================================
// ===========================================================================================================





/*
 *---------------------------------------------------------------
 * Identification Variable
 *---------------------------------------------------------------
 *
 * Used for Logging identification of the server it is running on
 *
 */
	define('IDEN', 'web1');
	define('SERVER_ID', 1);

        
/* End of file iden.php */
/* Location: ./iden.php */
