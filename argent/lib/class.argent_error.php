<?php
/**
 * Error Class
 * 
 * 
 * @package Argent CloudKit
 * @version 1.2.0
 * @since 1.0.1
 * @author Nick Cousins <me@nickcousins.co.uk>
 * @link http://www.argentcloudkit.com/documentation 
 */

if (!class_exists('argent_error'))
{
    class argent_error{
        
        private $errors;
        
        
        function __construct($code = NULL, $message = NULL, $data = NULL){
            $this->add($code,$message,$data);
        }
        
        
        
        
        /**
         * Add an error to the stack
         * 
         * @since 1.0.1
         * @param string $code
         * @param string $message
         * @param string $data
         * @param string $application 
         */
        function add($code = NULL, $message = NULL, $data = NULL, $application = 'argent_powered_app'){
            if (!empty($code) && !empty($message))
            {   
                $this->errors[]=array('code'=>$code, 'message'=>$message,'data'=>$data);
                argent_logger::log_error($code, $message, $data, $application);
            }
        }
        
        
        
        
        /**
         * Checks if there are any errors in this instance
         * 
         * @return boolean 
         */
        function has_errors(){
            if (count($this->errors)>0)
                return true;
            return false;
        }
        
        
        
        
        /**
         * Returns a multi-dimensional array of the errors in the stack
         * 
         * @param string $code Optional error code filter
         * @return array|false 
         */
        function read($code=NULL){
            if(empty($code))
            {
                return $this->errors;
            }
            else {
                
                $errors_for_code = array();
                
                if(count($this->errors)>0)
                    foreach($this->errors as $error)
                    {
                        if ($error['code']==$code)
                            $errors_for_code[]=$error;
                    }
                if (count($errors_for_code)>0)
                    return $errors_for_code;
            }
            return false;
        }
        
        
        
        
        /**
         * Verifies if an object is an instance of argent_error. Useful for
         * evaluating function responses
         * 
         * @static
         * @since 1.0.1
         * @param mixed $object
         * @return boolean 
         */
        public static function check($object){
            if (is_object($object) && is_a($object, 'argent_error'))
                    return true;
            return false;
        }
        
        
        
        
        /**
         * Exception Handler
         * 
         * @internal
         * @static
         * @param type $exception 
         */
        public static function catch_exception($exception){
            argent_logger::log_event('Exception', $exception->getMessage());
            echo "Argent Exception: ".$exception->getMessage();
        }
    }    
}