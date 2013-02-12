<?php 

/**
 *
 * Site Config Class, used for config variables that change per deployment but are static across all hosts
 *
 * ++++++++ THIS FILE IS STATIC PER BRANCH +++++++
 *
 * @version     site_conf v1
 * @level1      ./
 * 
 * @category    Controller
 * @author      Noah Spirakus
 * @Create      2011/11/11
 * @Modify      2011/11/11
 * @Project     ---
 * @link	required from  ./conf/conf.php
 * 			- require_once __DIR__.'/conf/conf.php';
 */



/*
 *---------------------------------------------------------------
 * APPLICATION ENVIRONMENT
 *---------------------------------------------------------------
 *
 * You can load different configurations depending on your
 * current environment. Setting the environment also influences
 * things like logging and error reporting.
 *
 * This can be set to anything, but default usage is:
 *
 *     development
 *     staging
 *     production
 *
 * This is static per deployment branch
 *
 */
    
    define('ENVIRONMENT', 'development');

    

/* End of file conf.php */
/* Location: ./conf/conf.php */
