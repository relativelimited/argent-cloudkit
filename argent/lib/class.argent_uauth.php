<?php
/**
 * User Authentication Class
 * 
 * 
 * @package Argent CloudKit
 * @subpackage argent_uauth
 * @version 1.2
 * @since 1.0.1
 * @author Nick Cousins <me@nickcousins.co.uk>
 * @link http://www.argentcloudkit.com/documentation 
 */

if (!class_exists('argent_uauth'))
{
    class argent_uauth{
        
        
        
        
        /**
         * Create a new account with a master user
         * 
         * @static
         * @since 1.0.1
         * @param string $email
         * @param string $secret
         * @param string $display_name
         * @param array $custom_fields
         * @return string|/argent_error 
         */
        public static function account_create($email = NULL, $secret = NULL, $display_name = NULL, $custom_fields = NULL){
            
            /* Create a new database connection */
            $db = new argent_database();
            
            /* Create a wsuam_error object */
            $error = new argent_error();
            
            $object_id = argent_identifier::object_id('ACC');
            
            $meta_guid = argent_identifier::meta_guid();
            
            $db->start_transaction();
            
            $sql =  "
                    INSERT INTO
                        `ua_accounts`
                    VALUES(
                        '{$db->escape_value($meta_guid)}',
                        '{$db->escape_value($object_id)}',
                        'NEW',
                        NOW(),
                        'USR-TEST',
                        '{$db->escape_value($_SERVER['REMOTE_ADDR'])}'
                    )
                    ";
            $db->query($sql);
            
            $account = array();
            
            $account['db']=&$db;
            $account['object_id'] = $object_id;
            
            $usr = self::user_create($account, $email, $secret, $display_name, $custom_fields);
            
            if (is_a($usr,'argent_error'))
            {
                    $db->rollback_transaction();
                    return $usr;
            }
            $db->end_transaction();
            return $object_id;
        }
        
        
        
        
        /**
         * Create a new user on an account
         * 
         * @static
         * @since 1.0.1
         * 
         * @param string $account ObjectID of the account to create user against
         * @param string $email Valid email address
         * @param string $secret Plaintext secret/password
         * @param string $display_name Display name for the user
         * @param array $custom_fields Array of custom fields to save for this user
         * @return \argent_error 
         */
        public static function user_create($account = NULL, $email = NULL, $secret = NULL, $display_name = NULL, $custom_fields = NULL){
            
            if (is_array($account))
            {
                $db = &$account['db'];
                $account = $account['object_id'];
                $end_transaction = false;
                
            }
            else{
                
                /* Create a new database connection */
                $db = new argent_database();
                $end_transaction = true;
                $db->start_transaction();
            }           
            
            /* Create a new error object to store errors in */
            $error = new argent_error();
            
            /**
             * Validate Inputs 
             */
            
            // Email is valid
            if  (!filter_var($email, FILTER_VALIDATE_EMAIL))
                $error->add('1001','Invalid email address',$email,'argent_uauth');
            
            // Password matches complexity requirements
            if  (!preg_match(UA_PASSWORD_COMPLEXITY,$secret))
                $error->add('1002',UA_PASSWORD_COMPLEXITY_ERROR,$secret,'argent_uauth');
            
            // Display name is not empty
            if  (empty($display_name))
                $error->add('1003','A display name must be specified','argent_uauth');
            
            // Email is not already registered to this account
                     
            if  (self::email_exists($email, NULL))
                $error->add('1004','Email address is already registered',$email,'argent_uauth');
            
            // Account Exists
            if  (!self::object_exists($account))
                $error->add('1005','Account does not exist',$account,'argent_uauth');
            
            
            /**
             * Check that validation is successful 
             */
            if (!$error->has_errors())
            {
                /* Successful Validation */
                
                
                
                // Generate an object_id and meta_guid
                
                $meta_guid = argent_identifier::meta_guid();
                $object_id = argent_identifier::object_id('USR');
                
                // Encrypt secret
                $secret = self::encrypt_secret($secret);
                
                
                // Insert User Data
                
                $sql =  "
                        INSERT INTO
                            `ua_users`
                        VALUES(
                            '{$db->escape_value($meta_guid)}',
                            '{$db->escape_value($object_id)}',
                            '{$db->escape_value($email)}',
                            '{$db->escape_value($secret)}',
                            '{$db->escape_value($display_name)}',
                            '{$db->escape_value($account)}',
                            NOW(),
                            'USR-TEST',
                            '{$db->escape_value($_SERVER['REMOTE_ADDR'])}'
                        )
                        ";
                $db->query($sql);
                
                if (is_array($custom_fields) && count($custom_fields > 0))
                {
                    foreach($custom_fields as $key=>$value)
                    {
                        $record = array();
                        $record['object_id']=  argent_identifier::object_id('CFL');
                        $record['field_name']=$db->escape_value($key);
                        $record['field_data']=$db->escape_value($value);
                        $record['ua_parent_object']=$object_id;
                        $record = argent_meta::add_meta($record);
                        argent_meta::save_record($record, 'ua_custom_fields');
                    }
                }
                
                if ($end_transaction)
                    $db->end_transaction();
            }
            else
            {
                /* Failed Validation */
                
                return $error;
            }
            
        }
        
        
        
        
        /**
         * Invokes PHPass to encrypt the given secret
         * 
         * @static
         * @since 1.0.1
         * @param string $secret
         * @return string 
         */
        public static function encrypt_secret($secret){
            $hasher = new PasswordHash(8, true);
            
            return $hasher->HashPassword($secret);
        }
        
        
        
        
        /**
         * Checks if a given email address exists
         * 
         * @static
         * @since 1.0.1
         * @param string $email
         * @param string $account Optional
         * @return boolean 
         */
        public static function email_exists($email = NULL, $account = NULL){
            
            if ($email == NULL)
                return false;
            
            $db = new argent_database();
            
            $sql =  "
                    SELECT
                        `ua_users_head`.`email`,
                        `ua_users_head`.`object_id`
                    FROM
                        `ua_users_head`
                    WHERE
                        `ua_users_head`.`email` = '{$db->escape_value($email)}'
                    ";
            if ($account != NULL)
                $sql .= "
                        AND
                            `ua_users_head`.`ua_parent_object` = '{$db->escape_value($account)}'
                        ";
            $data = $db->returnrow($sql);
            
            if ($data['email'] == $email)
                return $data['object_id'];
            
            return false;
        }
        
        
        
        
        /**
         * Checks if a given object (account or user) exists
         * 
         * @static
         * @since 1.0.1
         * @param string $object_id
         * @return boolean 
         */
        public static function object_exists($object_id){
            
            if (!is_string($object_id))
                return false;
            
            $object_type = substr($object_id,0,3);
            
            $db = new argent_database();
            
            switch ($object_type){
                case 'ACC':
                    $table = 'ua_accounts_head';
                    break;
                
                case 'USR':
                    $table = 'ua_users_head';
                    break;
                default:
                    return false;
            }
            
            $sql =  "
                    SELECT
                        `$table`.`object_id`
                    FROM
                        `$table`
                    WHERE
                        `$table`.`object_id` = '{$db->escape_value($object_id)}'
                    ";
            
            $data = $db->returnrow($sql);
            
            if ($data['object_id']==$object_id)
                return true;
            
            return false;
        }
        
        
        
        
        /**
         * Cleans up unused accounts
         * 
         * @static
         * @access private
         * @since 1.0.1
         * @internal
         * @todo Address issues with SQL statement 
         */
        private static function housekeeping(){
            $db = new argent_database();
            
            $timeout = strtotime(NEW_ACCOUNT_TIMEOUT.' hours ago');
            
            $dbtimeout = date('Y-m-d H:i:s',$timeout);
            
            $sql =  "
                    DELETE FROM
                        `ua_accounts`
                    WHERE
                        `ua_accounts`.`object_id`
                    IN
                        (
                            SELECT
                                `ua_accounts`.`object_id`
                            FROM
                                `ua_accounts`
                            WHERE
                                `ua_accounts`.`account_status` = 'NEW'
                            AND
                                `ua_accounts`.`meta_timestamp` <= '{$db->escape_value($dbtimeout)}'
                        )       
                    ";
             $db->query($sql);
        }
        
        
        
        
        /**
         * Returns the account ID which owns the currently logged-in user,
         * or false if no user is logged in.
         * 
         * @static
         * @since 1.0.1
         * @return string|false
         */
        public static function session_account(){
            return self::account_for(self::logged_in());
        }
        
        
        
        
        /**
         * Alias of argent_uauth::logged_in()
         * 
         * @static
         * @since 1.0.1
         * @return string|false 
         */
        public static function session_user(){
            return self::logged_in();
        }
        
        
        
        
        /**
         * Check if a user is currently logged into this session. Returns the
         * User ID or false.
         * 
         * @static
         * @since 1.0.1
         * @return string|false
         */
        public static function logged_in(){
            $ua_session =   new argent_session(SESSION_NAME, SESSION_TIMEOUT);
            
            $session_id =   $ua_session->id();
            
            $db =   new argent_database();
            
            $sql=   "
                    SELECT
                        *
                    FROM
                        `ua_session_register`
                    WHERE
                        `session_id` = '{$session_id}'
                    ";
            
             $session_reg = $db->returnrow($sql);
             
             if ($session_reg['session_id'] == $session_id)
                 return $session_reg['user_id'];
             
             return false;
        }
        
        
        
        
        /**
         * Returns the account that owns the specified User ID
         * 
         * @static
         * @since 1.0.1
         * @param string $user_id ua_users.object_id
         * @return string|false 
         */
        public static function account_for($user_id = NULL){
            if (self::object_exists($user_id))
            {
                $db =   new argent_database();
                
                $sql =  "
                        SELECT
                            `ua_parent_object`
                        FROM
                            `ua_users_head`
                        WHERE
                            `object_id` = '{$db->escape_value($user_id)}'
                        ";
                $data = $db->returnrow($sql);
                
                if (self::object_exists($data['ua_parent_object']))
                    return $data['ua_parent_object'];
            }
            
            return false;
        }
        
        
        
        
        /**
         * Authenticate the provided credentials and return the user-id or an
         * argent_error
         * 
         * @static
         * @version 1.1
         * @since 1.0.1
         * @param string $email
         * @param string $secret
         * @return string|\argent_error 
         */
        public static function authenticate($email = NULL, $secret = NULL){
            
            $error = new argent_error();
            
            if (filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($secret))
            {
                $db =   new argent_database();
                
                $sql=   "
                        SELECT
                            *
                        FROM
                            `ua_users_head`
                        WHERE
                            `email` = '{$db->escape_value($email)}'
                        ";
                
                $user_data = $db->returnrow($sql);
                
                if (count($user_data)<1)
                {
                    $error->add('1010','Email address does not exist',$email,'argent_uauth');
                    return $error;
                }
                
                $hasher = new PasswordHash(8, true);
                
                if ($hasher->CheckPassword($secret, $user_data['secret']))
                    return $user_data['object_id'];
                else
                {
                    $error->add('1011','Password incorrect','[password redacted]','argent_uauth');
                    return $error;
                }
                
            }
            else{
                $error->add('1012','You must specify a valid email address and password',null,'argent_uauth');
                    return $error;
            }
        }
        
        
        
        
        /**
         * Registers the current session against the specified user ID
         * 
         * @static
         * @access private
         * @since 1.0.1
         * @param string $user_id ua_users.object_id
         * @return boolean 
         */
        private static function register_user_session($user_id = NULL){
            
            if (!self::object_exists($user_id))
                return false;
            
            $ua_session =   new argent_session(SESSION_NAME, SESSION_TIMEOUT);
            
            $session_id =   $ua_session->id();
            
            $db =   new argent_database();
            
            self::unregister_user_session($session_id);
            
            $sql    =   "
                        INSERT INTO
                            `ua_session_register`
                        VALUES(
                            '{$db->escape_value($session_id)}',
                            '{$db->escape_value($user_id)}'
                        )
                        ";
            $db->query($sql);
            
            return true;
        }
        
        
        
        
        /**
         * Authenticates the user credentials and if successful, registers the
         * current session to that user
         * 
         * @static
         * @since 1.0.1
         * @version 1.1
         * @param string $email
         * @param string $secret
         * @return boolean 
         */
        public static function user_login($email = NULL, $secret = NULL){
            
            //self::housekeeping();
            
            $user_id = self::authenticate($email, $secret);
            
            if (argent_error::check($user_id))
                return $user_id;
            
            if (self::object_exists($user_id))
            {
                if (self::register_user_session ($user_id))
                    return $user_id;
            }
            
            return false;
            
        }
        
        
        
        
        /**
         * Unregisters the current user's session
         * 
         * @static
         * @since 1.0.1
         * @param type $session_id
         * @return boolean 
         */
        private static function unregister_user_session($session_id){
            
            $db =   new argent_database();
            
            $sql    =   "
                        DELETE FROM
                            `ua_session_register`
                        WHERE
                            `session_id` = '{$db->escape_value($session_id)}'
                        ";
                            
            $db->query($sql);
            
            return true;
        }
        
        
        
        
        /**
         * Un-registers the current user-session, destroys the session and unsets
         * the session cookie.
         * 
         * @static
         * @return true 
         * @since 1.0.1
         */
        public static function user_logout(){
            
            $ua_session =   new argent_session(SESSION_NAME, SESSION_TIMEOUT);
            
            $session_id =   $ua_session->id();
            
            self::unregister_user_session($session_id);
            
            $ua_session->end_session();
            
            return true;
        }
        
        
        
        
        /**
         * Updates the given user's password
         * 
         * @since 1.0.2
         * @param string $user_id
         * @param string $secret
         * @return boolean|\argent_error 
         */
        public static function user_password_update($user_id, $secret){
            
            $error  =   new argent_error();
            $db     =   new argent_database();
            
            if (!self::object_exists($user_id) || substr($user_id,0,3)!='USR')
                $error->add('1013','Invalid user account',$user_id,'argent_uauth');
            
            if  (!preg_match(UA_PASSWORD_COMPLEXITY,$secret))
                $error->add('1002',UA_PASSWORD_COMPLEXITY_ERROR,$secret,'argent_uauth');
            
            if (!$error->has_errors())
            {
                $sql    =   "
                            SELECT
                                *
                            FROM
                                `ua_users_head`
                            WHERE
                                `ua_users_head`.`object_id` = '{$db->escape_value($user_id)}'
                            ";
                $user_data = $db->returnrow($sql);
                
                // Duplicate the record;
                $user_data_new = $user_data;
                
                $user_data_new['secret'] = self::encrypt_secret($secret);
                
                // Save the record
                return argent_meta::save_record($user_data_new,'ua_users');
                
            }
            
            return $error;
            
            
        }
        
        
        
        
        /**
         * Returns an array of the user's data
         * 
         * @param string $user_id object_id of the user
         */
        public static function user_get_data($user_id){
            
            $db =   new argent_database();
            
            $error= new argent_error();
            
            if (self::object_exists($user_id))
            {
                $sql    =   "
                            SELECT
                                *
                            FROM
                                `ua_users_head`
                            WHERE
                                `object_id` = '{$db->escape_value($user_id)}'
                            ";
                $user_data = $db->returnrow($sql);
                
                if (count($user_data >0))
                {
                    return $user_data;
                }
                else
                {
                    return false;
                }
            }
            else
            {
                $error->add('1013', 'Invalid user account', $user_id, 'argent_uauth');
                return $error;
            }
        }
        
        
        
        
        /**
         * Returns any custom fields stored for the given user
         * 
         * @since 1.1.0
         * @static
         * @param string $user_id
         * @return boolean|\argent_error 
         */
        public static function user_get_additional_data($user_id){
            $db =   new argent_database();
            
            $error= new argent_error();
            
            if (self::object_exists($user_id))
            {
                $sql    =   "
                            SELECT
                                *
                            FROM
                                `ua_custom_fields_head`
                            WHERE
                                `object_id` = '{$db->escape_value($user_id)}'
                            ";
                $user_data = $db->returnrow($sql);
                
                if (count($user_data >0))
                {
                    return $user_data;
                }
                else
                {
                    return false;
                }
            }
            else
            {
                $error->add('1013', 'Invalid user account', $user_id, 'argent_uauth');
                return $error;
            }
        }
    }
}