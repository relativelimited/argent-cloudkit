<?php
/**
 * Main Configuration
 * 
 * 
 * @package Argent CloudKit
 * @version 1.2.0
 * @since 1.0.1
 * @author Nick Cousins <me@nickcousins.co.uk>
 * @link http://www.argentcloudkit.com/documentation 
 */





/**
 * ABSOLUTE_PATH constant - set this to the absolute path (relative to the
 * file system, not the web server, of the directory containing the /argent/ 
 * directory. Include a trailing slash.
 * 
 * For example, if this file is in /var/www/myargentapp.com/includes/argent/conf
 * set this constant to /var/www/myargentapp.com/includes/ 
 */
define('ABSOLUTE_PATH',$_SERVER['DOCUMENT_ROOT'].'argentcloudkit/argent-cloudkit/');




/**
 * Enable error logging. You must set write permissions on the log files before
 * enabling this feature.
 */
define('AG_ERROR_LOGGING',false);