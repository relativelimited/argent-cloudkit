<?php
/**
 * Identifier Class
 * 
 * 
 * @package Argent CloudKit
 * @version 1.2.0
 * @since 1.0.1
 * @author Nick Cousins <me@nickcousins.co.uk>
 * @link http://www.argentcloudkit.com/documentation 
 */

if (!class_exists('argent_identifier')){
    class argent_identifier{
        
        /**
         * Generates a meta_guid value for revision identifiers
         * 
         * @static
         * @since 1.0.1
         * @return string 
         */
        public static function meta_guid(){
            $recordid = microtime();

            $r = explode(' ',$recordid);

            $recordid = $r[1].($r[0] * 100000000).uniqid('_');

            return $recordid;
        }
        
        
        
        
        /**
         * Generates a prefixed UUID (version 4)
         * 
         * @static
         * @since 1.0.1
         * @param string $prefix 3-character prefix
         * @return string 
         */
        public static function object_id($prefix='OBJ'){
            
            if(strlen($prefix)<3)
                $prefix='OBJ';
            
            return strtoupper(substr($prefix,0,3)).'-'.sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
	
		  // 32 bits for "time_low"
		  mt_rand(0, 0xffff), mt_rand(0, 0xffff),
	
		  // 16 bits for "time_mid"
		  mt_rand(0, 0xffff),
	
		  // 16 bits for "time_hi_and_version",
		  // four most significant bits holds version number 4
		  mt_rand(0, 0x0fff) | 0x4000,
	
		  // 16 bits, 8 bits for "clk_seq_hi_res",
		  // 8 bits for "clk_seq_low",
		  // two most significant bits holds zero and one for variant DCE1.1
		  mt_rand(0, 0x3fff) | 0x8000,
	
		  // 48 bits for "node"
		  mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		);
        }
        
        
        
        
        /**
         * Generate a complete session identifier (with session verifier)
         * 
         * @static
         * @since 1.0.1
         * @param int $time Unix timestamp
         * @return string 
         */
        public static function session($time=NULL){
            
            if (empty($time))
                $time = time();
            
            return sprintf('%04x%04x%04x%04x%04x%04x%04x%04x',
	
		  // 32 bits for "time_low"
		  mt_rand(0, 0xffff), mt_rand(0, 0xffff),
	
		  // 16 bits for "time_mid"
		  mt_rand(0, 0xffff),
	
		  // 16 bits for "time_hi_and_version",
		  // four most significant bits holds version number 4
		  mt_rand(0, 0x0fff) | 0x4000,
	
		  // 16 bits, 8 bits for "clk_seq_hi_res",
		  // 8 bits for "clk_seq_low",
		  // two most significant bits holds zero and one for variant DCE1.1
		  mt_rand(0, 0x3fff) | 0x8000,
	
		  // 48 bits for "node"
		  mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		).'.'.self::session_verifier($time);
        }
        
        
        
        /**
         * Generates a session verifier hash based on a time provided
         * 
         * @static
         * @since 1.0.1
         * @version 1.2.0
         * @param int $time Unix timestamp
         * @return string 
         */
        public static function session_verifier($time){
            return sha1($time.'.'.$_SERVER['REMOTE_ADDR'].'.'.SESSION_SALT.'.'.$_SERVER['HTTP_USER_AGENT']);
        }
        
        
        
        
        /**
         * Returns the host name of the current machine
         * 
         * @static
         * @since 1.0.1
         * @return string 
         */
        public static function host(){
            if (function_exists('gethostname'))
            {
                return gethostname();
            }
            else
            {
                return php_uname('n');
            }
        }    
    }
}