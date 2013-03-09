<?php
/**
 * MySQL Database Driver
 * 
 * 
 * @package Argent CloudKit
 * @subpackage argent_database
 * @version 1.2.0
 * @since 1.0.1
 * @author Nick Cousins <me@nickcousins.co.uk>
 * @link http://www.argentcloudkit.com/documentation 
 */

class argent_mysqldatabase {
	
	private $connection;
	public $last_query;
	private $magic_quotes_active;
	private $real_escape_string_exists;
	
        
        
        
        
	/**
         * New mysqldatabase instance
         * 
         * @param array $parameters ['host','database','user','password']
         */
	function __construct($parameters=NULL)	{
            if ($parameters==NULL)
                {                       
                        $db=array();
                        $db['host']=AG_DB_HOST;
                        $db['database']=AG_DB_NAME;
                        $db['user']=AG_DB_USER;
                        $db['password']=AG_DB_PASS;
                        $parameters=$db;
                }
		$this->open_connection($parameters);
		$this->magic_quotes_active = get_magic_quotes_gpc();
		$this->real_escape_string_exists = function_exists("mysql_real_escape_string");
	}
	
        
        
        
	/**
         * Open a connection to the MySQL server
         * 
         * @since 1.0.1
         * @param array $parameters 
         */
	public function open_connection($parameters){
	
	$this->connection = mysql_connect($parameters['host'],$parameters['user'], $parameters['password']);
	if(!$this->connection){
		die("Database connection failed: ".mysql_error());
		
		} else 
		
		{
		$db_select = mysql_select_db($parameters['database'], $this->connection);
		if (!$db_select){
		die("Database selection failed: ".mysql_error());
			}

		}
	
	}
	
        
        
        
        /**
         * Close the current MySQL connection
         * 
         * @since 1.0.1 
         */
	public function close_connection(){
	
            if (isset($this->connection)){
                mysql_close($this->connection);
                unset($this->connection);
            }
	
	}

	
        
        
        /**
         * Query the database with the given SQL statement
         * 
         * @param string $sql
         * @return resource 
         */
	public function query($sql){
            if (QUERY_LOGGING)
                argent_logger::log_event('DB Query',$sql,'argent_database');
            
            $this->last_query=$sql;
            $result = mysql_query($sql, $this->connection);
            $this->confirm_query($result);
            return $result;

	}
	
	
        
        
        /**
         * If the query fails, throw an exception
         * 
         * @access private
         * @internal
         * @version 1.2.0
         * @since 1.0.1
         * @param resource $result 
         */
	private function confirm_query($result){
	
            if (!$result){
                $output = "Database query failed: ".mysql_error();
                $output.= "<br/><br/>Last query: ".$this->last_query;
                throw new Exception ($output);
            }
            
	}
	
        
        
        
        /**
         * Fetch an array from a given resource
         * 
         * @since 1.0.1
         * @param resource $result
         * @return array 
         */
	public function fetch_array($result){
	
            return  mysql_fetch_array($result);
		
	}
	
	
        
        
        /**
         * Count the number of rows returned in a given resource
         * 
         * @since 1.0.1
         * @param resource $result
         * @return integer 
         */
	public function num_rows($result){
	
		return mysql_num_rows($result);
	
	}
	
	
        
        
        /**
         * Retrieve the ID of the last inserted row
         * 
         * @since 1.0.1
         * @return int
         */
	public function insert_id(){
	
            return mysql_insert_id($this->connection);
	
	}
	
	
        
        
        /**
         * Return the number of rows affected by the last query
         * 
         * @since 1.0.1
         * @return int
         */
	public function affected_rows(){
	
            return mysql_affected_rows($this->connection);
	
	}
	
        
        
        
        /**
         * Make the given input value safe by escaping any unsafe characters
         * 
         * @since 1.0.1
         * @param string $value
         * @return string 
         */
	public function escape_value($value){
	
	
		if ($this->real_escape_string_exists){
		
				if ($this->magic_quotes_active)	{$value= stripslashes($value);}
			$value=mysql_real_escape_string($value);
			
		} else
		
		{
		if(!$this->magic_quotes_active) {$value = addslashes($value);}
		}
	
	return $value;
	}
	
        
        
        
        /**
         * Begin a transaction
         *  
         * @since 1.0.1
         */
	public function start_transaction(){
	
            mysql_query("BEGIN",$this->connection);
	
	}
	
        
        
        
        /**
         * End a transaction
         * 
         * @since 1.0.1 
         */
	public function end_transaction(){
	
            mysql_query("COMMIT",$this->connection);
	
	}
	
        
        
        
        /**
         * Roll back a transaction
         * 
         * @since 1.0.1
         */
	public function rollback_transaction(){
	
            mysql_query("ROLLBACK",$this->connection);
	
	}
	
        
        
        
        /**
         * Retrieve all rows from a resource as a multi-dimensional array
         * 
         * @since 1.0.1
         * @param resource $result
         * @return array 
         */
	public function get_resultset($result){
            $a = array();
	
            while ($b = mysql_fetch_assoc($result)){
                array_push($a,$b);    
            }
	
            return $a;
	}
	
        
        
        
        /**
         * Return a multi-dimensional array of results for a given SQL query
         * 
         * @since 1.0.1
         * @param string $query
         * @return array
         */
	public function returntable($query){
	
            $result = $this->query($query);
            $rows = $this->get_resultset($result);

            return $rows;
	
	}
	
        
        
        
        /**
         * Return an associative array of fields for the first matching result
         * for a given SQL query
         * 
         * @since 1.0.1
         * @param string $query
         * @return array|false 
         */
	public function returnrow($query){
	
            $result= $this->returntable($query);

            if (count($result)>0){
                return $result[0];    
            } 

            return false;
	}
        
        
        
        
        /**
         * Creates a head view for a given table name, after dropping any
         * existing head view as part of a transaction
         * 
         * @since 1.0.1
         * @param string $table_name 
         * @return true
         */
        public static function create_head_view($table_name){
            $db = new argent_database();
            
            $db->start_transaction();
            
            $sql    =   "
                        DROP VIEW IF EXISTS
                            `{$table_name}_head`
                        ";
                            
            $db->query($sql);
            
            $sql    =   "
                        CREATE VIEW 
                            `{$table_name}_head` 
                        AS 
                            SELECT 
                                `{$table_name}`.* 
                            FROM
                                `{$table_name}` 
                            WHERE 
                                `{$table_name}`.`meta_guid` 
                            IN
                                (SELECT max(`{$table_name}`.`meta_guid`) 
                                AS `max(meta_guid)` from `{$table_name}` group by `{$table_name}`.`object_id`)
                        ";
            
            $db->query($sql);
            
            $db->end_transaction();
            
            return true;
        }
        
        
        
        
        /**
         * Returns true if the given $table exists
         * 
         * @since 1.1.0
         * @param string $table
         * @return boolean 
         */
        function table_exists ($table){ 
	
            if (mysql_num_rows( mysql_query("SHOW TABLES LIKE '".$this->escape_value($table)."'")))
                    return true;
            
            return false;
        }
}

?>
