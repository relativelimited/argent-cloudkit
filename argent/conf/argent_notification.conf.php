<?php
/**
 * Notification Configuration
 * 
 * 
 * @package Argent CloudKit
 * @subpackage argent_notification
 * @version 1.0
 * @since 1.1.0
 * @author Nick Cousins <me@nickcousins.co.uk>
 * @link http://www.argentcloudkit.com/documentation 
 */




/**
 * Set the path to PHPMailer
 */
$path = $_SERVER['DOCUMENT_ROOT'].'/argent/phpmailer';
define('PHP_MAILER_PATH',$path);




/**
 * Set the default sender information for your application 
 */
define('NOTIFICATION_FROM_NAME','Argent Application');
define('NOTIFICATION_FROM_MAIL','no-reply@example.com');