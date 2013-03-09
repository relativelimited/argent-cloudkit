<?php
/**
 * Database Class
 * 
 * 
 * @package Argent CloudKit
 * @subpackage argent_database
 * @version 1.2.0
 * @since 1.0.1
 * @author Nick Cousins <me@nickcousins.co.uk>
 * @link http://www.argentcloudkit.com/documentation 
 */
/**
 * Include the database configuration 
 */
require_once(ABSOLUTE_PATH.'argent/conf/argent_database.conf.php');




/**
 * Import the MySQL driver
 */
require_once('class.argent_mysqldatabase.php');




/**
 * class_alias function for PHP < 5.3.0
 */
if (!function_exists('class_alias')) {
    function class_alias($original, $alias) {
        eval('class ' . $alias . ' extends ' . $original . ' {}');
    }
}




/**
 * Use MySQL as the default database
 * 
 * This statement can be used to switch database engines 
 */
class_alias('argent_mysqldatabase','argent_database');