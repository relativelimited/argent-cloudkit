<?php
/**
 * Session Class
 * 
 * 
 * @package Argent CloudKit
 * @subpackage argent_session
 * @version 1.2.0
 * @since 1.0.1
 * @author Nick Cousins <me@nickcousins.co.uk>
 * @link http://www.argentcloudkit.com/documentation 
 */
 
 require_once ('class.argent_identifier.php');
 require_once ('class.argent_database.php');
 require_once (ABSOLUTE_PATH.'argent/conf/argent_session.conf.php');
 
 /**
  * This global variable is used as a stand-in for the session
  * cookie so that sessions created in the current request are 
  * still accessible without having to close the connection and 
  * make another request with the cookie in.
  */
 $request_sessions = array();
 
 
 
 
 if (!class_exists('argent_session'))
     {
        class argent_session{

            private $id, $timeout, $name, $secure, $domain, $path, $data, $expiry;


            
            /**
             * argent_session
             * 
             * @param string $sessionName Name for the session cooke - default "SID"
             * @param int $timeout Inactivity timeout (seconds)
             * @param bool $secure HTTPS only
             * @param string $domain Session Cookie Domain
             * @param string $path Session Cookie Path
             */
            function __construct($sessionName=NULL, $timeout=NULL, $secure=false, $domain=NULL, $path=COOKIE_PATH){
                
                global $request_sessions;
                
                $this->data=array();
                $this->cleanup_sessions();

                if (empty($sessionName)){$sessionName='SID';}
                $this->name=$sessionName;
                
                if (isset($_COOKIE[$this->name]))
                    $session_id = $_COOKIE[$this->name];
                else if (isset($request_sessions[$this->name]))
                    $session_id = $request_sessions[$this->name];

                if (!empty($session_id)){
                    if (!$this->load_session($session_id))
                        {
                            // Session is Dead - Create New	
                            $this->create_session($sessionName, $timeout, $secure, $domain, $path);
                    }
                    else
                    {
                            // Session is Live - load it
                        if ($this->load_session($session_id)){
                            if (!headers_sent())
                                setcookie($this->name,$this->id,0,$this->path, $this->domain, $this->secure);
                        }

                        else

                        {
                            // Something's wrong - create a new session
                            $this->create_session($sessionName, $timeout, $secure, $domain, $path);
                        }
                    }
                }
                else
                {
                        // Create New Session	
                        $this->create_session($sessionName, $timeout, $secure, $domain, $path);
                }		
            }	

            
            
            
            /**
             * Create a session with a given name, timeout and properties
             * 
             * @param string $sessionName Default "SID"
             * @param int $timeout Inactivity timeout in seconds
             * @param bool $secure HTTPS only
             * @param string $domain Cookie domain - NULL for current and sub-domains
             * @param string $path Cookie path - NULL for current, or constant COOKIE_PATH
             */
            private function create_session($sessionName=NULL, $timeout=NULL, $secure=false, $domain=NULL, $path=COOKIE_PATH)
            {
                global $request_sessions;

                $db = new argent_database();

                $timenow = time();
                
                if (is_int($timeout))
                    $expiry = $timenow + $timeout;
                else
                    $expiry = $timenow + 900;
                
                $this->id   =  argent_identifier::session($timenow);
                if (!empty($domain))
                    {
                        if ($domain=='_DOMAIN_'){
                            preg_match('/^([w]{3}\.)?(.*)$/',$_SERVER['HTTP_HOST'],$m);
                            $this->domain='.'.$m[2];
                        }
                        else $this->domain=$domain;

                        } else{
                            preg_match('/^([w]{3}\.)?(.*)$/',$_SERVER['HTTP_HOST'],$m);
                            $this->domain='.'.$m[2];
                        }

                if (!empty($path)){$this->path=$path;}
                if ($secure==false){$this->secure=false;} else {$this->secure=true;}
                if ($timeout == null || $timeout<900) {$timeout=900;} 
                $this->timeout=$timeout;

                $this->secure = (int)$this->secure;


                $query =    "INSERT INTO 
                                `ua_sessions`
                             VALUES(
                                '{$this->id}',
                                '{$db->escape_value($this->name)}',
                                {$this->timeout},
                                {$timenow},
                                {$timenow},
                                '{$db->escape_value($_SERVER['REMOTE_ADDR'])}',
                                {$this->secure},
                                '{$db->escape_value($this->domain)}',
                                '{$db->escape_value($this->path)}',
                                '{$db->escape_value($_SERVER['HTTP_USER_AGENT'])}',
                                '')";

                $db->query($query);

                unset($db);

                $request_sessions[$this->name]   =   $this->id;

                if (!headers_sent())
                    setcookie($this->name,$this->id,0,$this->path, $this->domain, $this->secure);

            }

            
            
            
            /**
             * Load the session from the database into the object instance
             * 
             * @access private
             * @internal
             * @version 1.2.0
             * @since 1.0.1
             * @param string $sessionID
             * @return boolean 
             */
            private function load_session($sessionID)
            {
                $db = new argent_database();

                $query  =   "
                            SELECT 
                                * 
                            FROM 
                                `ua_sessions` 
                            WHERE 
                                `session_id` = '{$db->escape_value($sessionID)}'
                            AND
                                `session_name` = '{$db->escape_value($this->name)}'
                            AND 
                                `userAgent` = '{$db->escape_value($_SERVER['HTTP_USER_AGENT'])}'";

                $sessionData = $db->returnrow($query);

                /**
                 * Check the session checksum for validity 
                 */
                $session_verifier = argent_identifier::session_verifier($sessionData['started']);
                $session_checksum = substr($sessionData['session_id'], 33);                
                if ($session_verifier!=$session_checksum)
                    return false;
                
                

                if ($sessionData['session_id']==$sessionID && $sessionData['last_activity'] > (mktime() - $sessionData['timeout']))
                {
                    $this->id=$sessionData['session_id'];
                    $this->timeout = $sessionData['timeout'];
                    $this->secure = (bool) $sessionData['secure'];
                    $this->domain = $sessionData['domain'];
                    $this->expiry = $sessionData['last_activity'] + $sessionData['timeout'];
                    $this->path = $sessionData['path'];
                    $this->data = unserialize($sessionData['data']);
                    $query  =   "
                                UPDATE 
                                    `ua_sessions`
                                SET 
                                    `last_activity` = ".mktime()."
                                WHERE
                                    `session_id` = '{$db->escape_value($sessionID)}'
                                AND 
                                    `session_name` = '{$db->escape_value($this->name)}'";
                                    
                    $db->query($query);

                    unset($db);

                    return true;
                }
                else
                {
                    unset($db);

                    return false;
                }
            }

            
            
            
            /**
             * Removes any timed-out sessions
             * 
             * @internal
             * @access private
             * @since 1.0.1
             */
            private function cleanup_sessions()
            {
                    $db = new argent_database();
                    
                    $query  =   "
                                DELETE FROM
                                    `ua_sessions`
                                WHERE 
                                    `last_activity` < (NOW() - `timeout`)";

                    $db->query($query);
                    
                    $query  =   "
                                DELETE FROM
                                    `ua_session_register`
                                WHERE 
                                    `session_id`
                                NOT IN
                                    (
                                        SELECT
                                            `session_id`
                                        FROM
                                            `ua_sessions`
                                    )";

                    $db->query($query);
            }

            
            
            
            /**
             * Set a variable into the session
             * 
             * @since 1.0.1
             * @param string $variable Variable name
             * @param mixed $value Variable value 
             */
            public function set($variable, $value)
            {
                    $this->data[$variable]=$value;
                    $db = new argent_database();

                    $query  =   "
                                UPDATE 
                                    `ua_sessions`
                                SET
                                    `data` = '".serialize($this->data)."'
                                WHERE 
                                    `session_id` = '{$db->escape_value($this->id)}'
                                AND
                                    `session_name` = '{$db->escape_value($this->name)}'";
                    $db->query($query);
                    $this->load_session($this->id);

                    unset ($db);
            }
            
            
            
            
            /**
             * Clears the current session from the database, and unsets the
             * session cookie
             * 
             * @since 1.0.1
             * @return true
             */
            public function end_session(){
                $db = new argent_database();

                $query  =   "
                            DELETE FROM 
                                `ua_sessions`
                            WHERE 
                                `session_id` = '{$db->escape_value($this->id)}'
                            AND
                                `session_name` = '{$db->escape_value($this->name)}'";

                $db->query($query);
                
                if (!headers_sent())
                    setcookie($this->name,'',time()-3600,$this->path, $this->domain, $this->secure);

                return true;
                    
            }

            // Accessors
            
            /**
             * Return the session ID
             * 
             * @since 1.0.1
             * @return string 
             */
            public function id(){
                return $this->id;    
            }
            
            
            
            
            /**
             * Return the session cookie name
             * 
             * @since 1.0.1
             * @return string
             */
            public function name(){
                return $this->name;    
            }
            
            
            
            
            /**
             * Return the UNIX timestamp when the session will expire
             * 
             * @since 1.0.1
             * @return int 
             */
            public function expiry(){
                return $this->expiry;
            }
            
            
            
            
            /**
             * Return the value of the given variable
             * 
             * @since 1.0.1
             * @param string $variable
             * @return mixed
             */
            public function get($variable=NULL){
                if ($variable==NULL) 
                    {return $this->data;} 
                else 
                    {if (isset($this->data[$variable])) 
                         {return $this->data[$variable];} 
                     else 
                         {return false;}    
                    }
            }


                // End Class
        }	

    }