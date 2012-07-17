<?php
/**
 * Argent CloudKit - Main Module
 * 
 * 
 * @package Argent CloudKit
 * @version 1.1.0
 * @since 1.0.1
 * @author Nick Cousins <me@nickcousins.co.uk>
 * @link http://www.argentcloudkit.com/documentation 
 */



/**
 * Define system-managed object types 
 */
        define('SYSTEM_TYPES',serialize(array(
                                    'USR'=>'User',
                                    'ACC'=>'Account',
                                    'CFL'=>'Custom field'
                                    )));




/**
 * Load all required classes & dependencies
 */

        function include_all_php($folder){
            foreach (glob("{$folder}/*.php") as $filename)
            {
                require_once $filename;
            }
        }

        /**
         * Load configuration
         */
        include_all_php(dirname(__FILE__).'/conf');
        
        /**
         * Load dependencies
         */
        include_all_php(dirname(__FILE__).'/dependencies');
        
        /**
         * Load all libraries 
         */
        include_all_php(dirname(__FILE__).'/lib');

        
        
        
/**
* Set the exception handler 
*/
        set_exception_handler('argent_error::catch_exception');    
        
        
        
        
/**
 * Set the X-Powered-By Header 
 * 
 * @since 1.1.0
 */
        header('X-Powered-By: Argent CloudKit/1.1.0, PHP/'.  phpversion());