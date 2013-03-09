<?php
/**
 * Memcache Class
 * 
 * 
 * @package Argent CloudKit
 * @subpackage argent_memcache
 * @version 1.2.0
 * @since 1.2.1
 * @author Nick Cousins <me@nickcousins.co.uk>
 * @link http://www.argentcloudkit.com/documentation 
 */
/**
 * Include the Memcache configuration 
 */

if (!class_exists('argent_memcache'))
{
require_once(ABSOLUTE_PATH.'argent/conf/argent_memcache.conf.php');
        
    class argent_memcache{      
        
        
        /**
         * Get a value for a key
         * 
         * @global type $memcache
         * @param string $key
         * @param callable $callback
         * @param boolean $cas_token
         * @return boolean 
         */
        public static function get($key,$callback=null,$cas_token=null){
            global $memcache;
            
            return $memcache->get($key,$callback);
        }
        
        
        
        
        /*
         * Get many keys
         */
        public static function multiGet($keys,$cas_tokens=null,$flags=null){
            global $memcache;
            
            return $memcache->getMulti($keys,$cas_tokens,$flags);
        }
        
        
        
        
        /*
         * Enqueues a related key which must be expired when
         * the object expires.
         * 
         * @param   $object_id  string  ObjectID to which the key relates
         * @param   $key        string  Key in cache which must be added to expiry list
         * @return  boolean
         */
        public static function enqueue_key($object_id,$key){
            global $memcache;
            
            $buster_id = 'ag_exp_'.$object_id;
            
            $buster = $memcache->get($buster_id);
            
            if (!$buster){
                $memcache->set($buster_id,serialize(array($key)));
            }
            else
            {
                $bustkeys = unserialize($buster);
                if (!in_array($key,$bustkeys))
                {
                    $bustkeys[]=$key;
                    $memcache->set($buster_id,serialize($bustkeys));
                }
            }
        }
        
        
        
        
        /**
         * Expire an object and all its enqueued keys
         * 
         * @global type $memcache
         * @param string $object_id
         * @return boolean 
         */
        public static function expire_all($object_id){
            global $memcache;
            
            $buster_id = 'ag_exp_'.$object_id;
            
            $buster = $memcache->get($buster_id);
            
            if (!$buster){
                return false;
            }
            else{
                $bust_keys = unserialize($buster);
                if (count($bust_keys)>0)
                    foreach($bust_keys as $key){
                        $memcache->delete($key);
                    }
                $memcache->delete($buster_id);
                $memcache->delete($object_id);
                return true;
            }
        }
        
        
        
        /**
         * Add a value to a new key
         * 
         * @global type $memcache
         * @param string $key
		 * @param mixed $value
		 * @param integer $expiration
         * @return boolean 
         */
        public static function add($key,$value,$expiration=0){
            global $memcache;
            
            return $memcache->add($key,$value,$expiration);
        }
        
        
		/**
         * Set a value for a key
         * 
         * @global type $memcache
         * @param string $key
		 * @param mixed $value
		 * @param integer $expiration
         * @return boolean 
         */
        public static function set($key,$value,$expiration=0){
            global $memcache;
            
            return $memcache->set($key,$value,$expiration);
        }
        
        
		/**
         * Append a value for a key
         * 
         * @global type $memcache
         * @param string $key
		 * @param mixed $value
         * @return boolean 
         */
        public static function append($key,$value){
            global $memcache;
            
            return $memcache->append($key,$value);
        }
		
		
		
        /**
         * Enqueue a key for many objects
         * 
         * @global type $memcache
         * @param array $objects_list array of object_ids
         * @param string $key
         * @return boolean 
         */
        public static function enqueue_keys($objects_list,$key){
                global $memcache;

                // Instantiate an array to store the ag_exp_ IDs in
                $buster_ids = array();

                // Iterate through all of the object_ids in the list and create ag_exp_ ids for them
                foreach($objects_list as $object_id){
                        $buster_ids[] = "ag_exp_$object_id";
                }

                // Return all of the enqueued keys for each object from cache
                $busters = $memcache->getMulti($buster_ids);

                // Iterate through each list of keys and add $key to the inverted index
                if (count($busters)>0)
                foreach($busters as $buster){
                        if (!$buster){
        $memcache->set($buster_id,serialize(array($key)));
                        }
                        else
                        {
                                $bustkeys = unserialize($buster);
                                if (!in_array($key,$bustkeys))
                                {
                                        $bustkeys[]=$key;
                                        $memcache->set($buster_id,serialize($bustkeys));
                                }
                        }
                }
        }

		
    }
}