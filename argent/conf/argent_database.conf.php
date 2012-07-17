<?php
/**
 * Database Configuration
 * 
 * 
 * @package Argent CloudKit
 * @subpackage argent_database
 * @version 1.1
 * @since 1.0.1
 * @author Nick Cousins <me@nickcousins.co.uk>
 * @link http://www.argentcloudkit.com/documentation 
 */

/**
 * MySQL Database Configuration
 * 
 * DB_HOST, DB_NAME, DB_USER and DB_PASS can be specified per-host, by entering
 * a case for each potential host
 */


/**
 * Include Argent Identifier 
 */
require_once(ABSOLUTE_PATH.'argent/lib/class.argent_identifier.php');


/**
 * Database Credentials
 * =============================================
 * 
 * Inside the switch is a default case, if you have only one database fill in
 * each constant with the appropriate value.
 * If you wish different application hosts to access different database servers
 * or use different credentials, create a new case with the same properties
 * for each host, specifying the correct credentials for each case. Replace
 * the 'your_host_name' in each case with the system host name, not the HTTPd
 * virtual host name.
 * 
 *  
 */
$hostname   = argent_identifier::host();

switch ($hostname){
    case 'your_host_name':
    default :
        define('AG_DB_HOST','localhost');
        define('AG_DB_NAME','database_user');
        define('AG_DB_USER','database_name');
        define('AG_DB_PASS','password');
        break;
}




/**
 * Enable Query Logging
 * You must set write permissions on the events.log file before enabling this
 * feature.
 * NOTE: This is for debugging purposes only - you should disable this in 
 * production applications as it will generate huge log files. 
 */
define('QUERY_LOGGING',false);