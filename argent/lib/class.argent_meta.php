<?php
/**
 * Meta Class
 * 
 * 
 * @package Argent CloudKit
 * @subpackage argent_meta
 * @version 1.2
 * @since 1.0.2
 * @author Nick Cousins <me@nickcousins.co.uk>
 * @link http://www.argentcloudkit.com/documentation 
 */

if (!class_exists('argent_meta'))
{
    class argent_meta{
        
        /**
         * Refresh the meta data in the given record prior to saving
         * 
         * @static
         * @since 1.0.2
         * @param array $record
         * @return array|\argent_error 
         */
        public static function update_record_meta($record=NULL){
           
            $error = new argent_error();
            
            if (self::verify_meta($record))
            {
                $record['meta_user'] = argent_uauth::session_user();
                $record['meta_ip'] = $_SERVER['REMOTE_ADDR'];
                $record['meta_timestamp'] = date('Y-m-d H:i:s');
                $record['meta_guid'] = argent_identifier::meta_guid();
                
                return $record;
            }
            
            $error->add('1030', 'Invalid Meta Record', $record, 'argent_meta');
            
            return $error;
            
        }
        
        
        
        
        /**
         * Validate that meta data exists in the given record
         * 
         * @param array $record
         * @return boolean 
         */
        public static function verify_meta($record=NULL){
            if (
                is_array($record) &&
                array_key_exists('meta_guid', $record) &&
                array_key_exists('meta_user', $record) &&
                array_key_exists('meta_ip', $record) &&
                array_key_exists('meta_timestamp', $record)
                )
                    return true;
            return false;
        }
        
        
        
        
        /**
         * Adds argent meta data to a new record
         * 
         * @static
         * @since 1.1.0
         * @param array $record
         * @return array 
         */
        public static function add_meta($record){
            $record['meta_user'] = argent_uauth::session_user();
            $record['meta_ip'] = $_SERVER['REMOTE_ADDR'];
            $record['meta_timestamp'] = date('Y-m-d H:i:s');
            $record['meta_guid'] = argent_identifier::meta_guid();
            
            return $record;
        }
        
        
        
        
        /**
         * Returns a table containing all revisions for a given object with the
         * most recent first
         * 
         * @static
         * @since 1.1.0
         * @param string $object_id
         * @param string $table
         * @return array 
         */
        public static function revisions_for($object_id,$table){
            
            $db = new argent_database();
            
            $sql =  "
                    SELECT
                        *
                    FROM
                        `{$db->escape_value($table)}`
                    WHERE
                        `object_id` = '{$db->escape_value($object_id)}'
                    ORDER BY
                        `meta_guid` DESC
                    ";
            
            $revisions = $db->returntable($sql);
            
            return $revisions;
        }
        
        
        
        
        /**
         * Reverts an object back to a prior revision
         * 
         * @static
         * @since 1.1.0
         * @param string $object_id The object ID
         * @param string $table The database table name
         * @param string $revision The meta_guid of the revision
         * @return resource|\argent_error DB resource if successful
         */
        public static function revert($object_id, $table, $revision){
            
            $db = new argent_database();
            
            $error = new argent_error();
            
            $revisions = self::revisions_for($object_id, $table);
            
            if (count($revisions)>0)
                foreach($revisions as $rev)
                {
                    if ($rev['meta_guid'] == $revision)
                    {
                        $revision_data=$rev;
                        break;
                    }
                    else
                    {
                        $error->add ('1031', 'Invalid revision ID for object', $object.' > '.$revision, 'argent_meta');
                        return $error;
                    }
                }
            else
            {
                $error->add('1032', 'Invalid object ID for table', $table.' > '.$object_id, 'argent_meta');
                return $error;
            }
            
            $rev = self::update_record_meta($revision_data);
            
            return self::save_record($rev,$table);
        }
        
        
        
        
        /**
         * Updates a given record in a table
         * Moved from argent_database in 1.1.0
         * 
         * @since 1.1.0
         * @static
         * @param type $record_data
         * @param type $table
         * @return type 
         */
        public static function save_record($record_data,$table){
            
            $db = new argent_database();
            
            $record_data = self::update_record_meta($record_data);
            
            if (argent_error::check($record_data))
                return $record_data;
            
            
            $sql    =   "
                            INSERT INTO
                                `{$db->escape_value($table)}`
                            (";
                if(count($record_data)>0)
                {
                    $asql ="";
                
                    foreach($record_data as $key=>$value)
                    {
                        if (!empty($asql))
                            $asql.=",";
                    
                        $asql.="`{$db->escape_value($key)}`";
                    }
                }
             $sql.=$asql;
             $sql.= ")   VALUES(
                            ";
                $i  =   0;
                foreach($record_data as $field=>$value)
                {
                    if ($i>0)
                        $sql.=  ",";
                    
                    $sql.=  "
                            '{$db->escape_value($value)}'";
                    $i++;        
                }
                
                $sql   .=   "
                                )
                            ";
                
                return $db->query($sql);
            
        }
        
        
        
        
        /**
         * Return a specified revision of an object
         * 
         * @since 1.1.0
         * @static
         * @param string $object_id
         * @param string $table
         * @param string $revision 
         */
        public static function get_revision($object_id, $table, $revision=NULL){
            
            $error = new argent_error();
            
            $revisions = self::revisions_for($object_id, $table);
            
            $revision_data = array();
            
            if (count($revisions)>0)
                if (empty($revision))
                    {
                        return array_shift($revisions);
                    }
                else
                foreach($revisions as $rev)
                {                   
                    if ($rev['meta_guid'] == $revision)
                    {
                        $revision_data=$rev;
                        break;
                    }
                    else
                    {
                        $error->add ('1031', 'Invalid revision ID for object', $object.' > '.$revision, 'argent_meta');
                        return $error;
                    }
                }
            else
            {
                $error->add('1032', 'Invalid object ID for table', $table.' > '.$object_id, 'argent_meta');
                return $error;
            }
            
            return $revision_data;
            
        }
        
        
        
        
        
        public static function set_field($object_id, $field_name, $value=NULL){
            $db =   new argent_database();
            
            $error =    new argent_error();
            
            $sql    =   "
                        SELECT
                            *
                        FROM
                            `ua_custom_fields_head`
                        WHERE
                            `ua_parent_object`  =   '{$db->escape_value($object_id)}'
                        AND
                            `field_name`    =   '{$db->escape_value($field_name)}'
                        ";
            
            $record =   $db->returnrow($sql);
            
            if (!empty($record['meta_guid']))
                $record['field_data'] = $db->escape_value($value);
            
            else
            {
                $record=array('object_id'=>argent_identifier::object_id('CFL'),'ua_parent_object'=>$object_id,'field_name'=>$field_name,'field_data'=>$value);
                $record = argent_meta::add_meta($record);
            }
            
            return argent_meta::save_record($record, 'ua_custom_fields');
        }
        
        
        
        
        /**
         * Register a new object type
         * 
         * @static
         * @since 1.1.0
         * @param string $type_identifier 3-character object_id prefix
         * @param string $type_name 
         * @return true|/argent_error 
         */
        public static function register_object_type($type_identifier=NULL, $type_name=NULL, $table=NULL){
            $db =   new argent_database();
            
            $error  =   new argent_error();
            
            // Check the identifier is set
            if (empty($type_identifier) || strlen($type_identifier)!=3)
                $error->add ('1033', 'Invalid type identifier', $type_identifier, 'argent_meta');
            
            // Check the type name is set
            if (empty($type_name) || !is_string($type_name) || is_numeric(substr($type_name,0,1)))
                $error->add ('1034', 'Invalid type name', $type_name, 'argent_meta');
            
            // Check it's not a system type
            if (self::is_system_type($type_identifier))
                $error->add ('1037', 'System type cannot be modified', $type_identifier.' > '.$type_name, 'argent_meta');
            
            // Check if the given table exists
            if (!$db->table_exists($table))
                $error->add('1050','Table does not exist',$table,'argent_meta');
            
            // Check that the object type is not already registered
            $sql    =   "
                        SELECT
                            *
                        FROM
                            `ua_object_types`
                        WHERE
                            `type_identifier` = '{$db->escape_value(strtoupper($type_identifier))}'
                        OR
                            `type_name` LIKE '{$db->escape_value($type_name)}'
                        ";
            $data = $db->returntable($sql);
            
            if (count($data)>0)
                $error->add ('1035', 'A similar object type definition already exists', $type_identifier.' > '.$type_name, 'argent_meta');
            
            if ($error->has_errors())
                return $error;
            
            $sql    =   "
                        INSERT INTO
                            `ua_object_types`
                        VALUES(
                            '{$db->escape_value(strtoupper($type_identifier))}',
                            '{$db->escape_value($type_name)}',
                            '{$db->escape_value($table)}'
                        )
                        ";
            $res = $db->query($sql);
            if ($res)
                return true;
            $error->add ('1000', 'Database error', $db->lastquery, 'argent_meta');
            return $error;
        }
        
        
        
        
        /**
         * Checks that a supplied type_identifier (3-character prefix) is registered
         * 
         * @static
         * @since 1.1.0
         * 
         * @param string $type_identifier 3-character object_id prefix
         * @return boolean 
         */
        public static function valid_object_type($type_identifier){
            $db =   new argent_database();
            
            // Check the identifier is set
            if (empty($type_identifier) || strlen($type_identifier)!=3)
                return false;
            
            if (self::is_system_type($type_identifier))
                return true;
            
            $sql    =   "
                        SELECT
                            *
                        FROM
                            `ua_object_types`
                        WHERE
                            `type_identifier` = '{$db->escape_value(strtoupper($type_identifier))}'
                        ";
                            
            $data = $db->returnrow($sql);
            
            if (!empty($data['type_identifier']))
                return $data['type_name'];
            
            return false;
        }
        
        
        
        
        /**
         * Unregister an object type identifier
         * 
         * @param string $type_identifier 3-character object_id prefix
         * @return \argent_error|true 
         */
        public static function unregister_object_type($type_identifier){
            $db =   new argent_database();
            
            $error  =   new argent_error();
            
            if (!self::valid_object_type($type_identifier))
                $error->add('1036','Unregistered object type',$type_identifier,'argent_meta');
            
            if (self::is_system_type($type_identifier))
                $error->add ('1037', 'System type cannot be modified', $type_identifier.' > '.$type_name, 'argent_meta');
            
            if ($error->has_errors())
                return $error;
            
            $sql    =   "
                        DELETE FROM
                            `ua_object_types`
                        WHERE
                            `type_identifier` =   '{$db->escape_value(strtoupper($type_identifier))}'
                        ";
            $res    =   $db->query($sql);
            
            if (!$res)
            {
                $error->add('1000','Database Error',$sql,'argent_meta');
                return $error;
            }
            
            return true;
        }
        
        
        
        
        /**
         * Checks if a given type_identifier is a system type
         * 
         * @static
         * @since 1.1.0
         * 
         * @param string $type_identifier 3-character object_id prefix
         * return boolean
         */
        public static function is_system_type($type_identifier){
            $r  =   unserialize(SYSTEM_TYPES);
            
            if (array_key_exists($type_identifier, $r))
            {   
                return $r[$type_identifier];
            }
            return false;
        }
        
        
        
        
        public static function register_object($object_type=NULL,$ua_parent_object=NULL){
            $error  =   new argent_error();
            
            if (!self::valid_object_type($object_type))
                $error->add('1036','Unregistered object type',$object_type,'argent_meta');
            
            if ($error->has_errors())
                return $error;
            
            $db =   new argent_database();
            
            $object_id  = argent_identifier::object_id($object_type);
            
            $sql    =   "
                        INSERT INTO
                            `ua_object_register`
                        (
                            `object_id`,
                            `ua_parent_object`
                        )
                        VALUES(
                            '{$db->escape_value($object_id)}',
                            '{$db->escape_value($ua_parent_object)}'
                        )
                        ";
            $res    =   $db->query($sql);
            
            if (!$res)
            {
                $error->add('1000','Database Error',$sql,'argent_meta');
                return $error;
            }
            
            return $object_id;            
        }
        
        
        
        
        public static function object_registered($object_id){
            $db =   new argent_database();
            
            // Check the identifier is set
            if (empty($object_id) || strlen($object_id)!=40)
                return false;
            
            if (argent_uauth::object_exists($object_id))
                return true;
            
            $sql    =   "
                        SELECT
                            *
                        FROM
                            `ua_object_register`
                        WHERE
                            `object_id` = '{$db->escape_value(strtoupper($object_id))}'
                        ";
                            
            $data = $db->returnrow($sql);
            
            if (!empty($data['object_id']))
                return true;
            
            return false;
        }
        
        
        
        
        public static function get_custom_fields($object_id=NULL){
            $error  =   new argent_error();
            
            if (!self::object_registered($object_id))
                $error->add('1038','Object does not exist',$object_id,'argent_meta');
            
            if ($error->has_errors())
                return $error;
            
            $db =   new argent_database();
            
            $sql    =   "
                        SELECT
                            *
                        FROM
                            `ua_custom_fields_head`
                        WHERE
                            `ua_parent_object`  =   '{$db->escape_value($object_id)}'
                        ";
                            
            $data   =   $db->returntable($sql);
            
            $fields =   array();
            
            if (count($data)>0)
                foreach($data as $field)
                {
                    $fields[$field['field_name']]=$field['field_data'];
                }
                
            return $fields;
        }
        
        
        
        
        /**
         * Returns the type description information for a given type_identifier
         * 
         * @param string $type_identifier
         * @return array\argent_error 
         */
        public static function type_info($type_identifier=NULL){
            
            $db =   new argent_database();
            
            $error  =   new argent_error();
            
            if (!self::valid_object_type($type_identifier))
                $error->add('1036','Unregistered object type',$type_identifier,'argent_meta');
            
            if ($error->has_errors())
                return $error;
            
            if (self::is_system_type($type_identifier))
            {
                $stypes =   unserialize(SYSTEM_TYPES);
                $type_info  =   array();
                $type_info['type_identifier']=  strtoupper($type_identifier);
                $type_info['type_name'] = $stypes[$type_identifier];
                switch($type_identifier){
                    case 'USR':
                        $type_info['table'] =   'ua_users';
                        break;
                    case 'ACC':
                        $type_info['table'] =   'ua_accounts';
                        break;
                    case 'CFL':
                        $type_info['table'] =   'ua_custom_fields';
                        break;
                }
                
            }
            else
            {
                $sql    =   "
                            SELECT
                                *
                            FROM
                                `ua_object_types`
                            WHERE
                                `type_identifier`   =   '{$db->escape_value($type_identifier)}'
                            ";
                                
                $type_info  =   $db->returnrow($sql);
                
                if (!$type_info)
                {
                    $error->add('1000','Database Error',$sql,'argent_meta');
                    return $error;
                }
            }
            
            return $type_info;
        }
    }
}
