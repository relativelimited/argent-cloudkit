<?php
/**
 * Argent CloudKit - Main Module
 * 
 * Copyright 2012-2013 Nick Cousins
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * @package Argent CloudKit
 * @version 1.2.0
 * @since 1.0.1
 * @author Nick Cousins <me@nickcousins.co.uk>
 * @link http://www.argentcloudkit.com/documentation 
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * 
 */



/**
 * Define system-managed object types 
 */
        define('SYSTEM_TYPES',serialize(array(
                                    'USR'=>'User',
                                    'ACC'=>'Account',
                                    'CFL'=>'Custom field',
                                    'URT'=>'User Right'
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
        header('X-Powered-By: Argent CloudKit/1.2.0, PHP/'.  phpversion());