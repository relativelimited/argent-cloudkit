<?php
/**
 * Logger Class
 * 
 * 
 * @package Argent CloudKit
 * @subpackage argent_logger
 * @version 1.2.0
 * @since 1.0.1
 * @author Nick Cousins <me@nickcousins.co.uk>
 * @link http://www.argentcloudkit.com/documentation 
 */

if (!class_exists('argent_logger'))
{
    class argent_logger{
        
        
        
        
        /**
         * Writes raw strings into the appropriate log file
         * 
         * @access private
         * @static
         * @since 1.0.1
         * @param string $log Log file to use; "error" or "event"
         * @param string $string String to enter into the log 
         */
        private static function write_to_log($log,$string){
            
            switch ($log){
                case 'error':
                    $log_file = "error.log";
                    break;
                case 'event':
                    $log_file = "events.log";
                    break;
                default :
                    $error = new argent_error();
                    $error->add('0010','Invalid log specified',$log);
                    return $error;
            }
            
            $log_file = ABSOLUTE_PATH.'argent/logs/'.$log_file;
            
            
            if (!is_writable($log_file))
                die('Argent: Log file not writable: '.$log_file);
            
            $log = fopen($log_file,'a');
            
            fwrite($log, $string."\r\n");
            
        }
        
        
        
        /**
         * Log an error
         * 
         * @static
         * @since 1.0.1
         * @param string $code Error code
         * @param string $message Error message
         * @param string $payload Data related to or causing the error
         * @param string $application The name of your module e.g. "Search API"
         * @return boolean 
         */
        public static function log_error($code,$message="",$payload=NULL,$application='argent_powered_app'){
            
            if (argent_error::check($code))
            {
                if ($code->has_errors())
                {
                    $errors = $code->read();
                    
                    if (!empty($errors))
                    {
                        foreach($errors as $code=>$error)
                        {
                            $message = $error['message'];
                            $payload = $error['data'];
                            
                            /* Timestamp */
                            $errmsg = date('Y-m-d H:i:s');

                            /* Process */
                            $errmsg.= "\t[{$application}]\t";

                            /* IP */
                            $errmsg.= $_SERVER['REMOTE_ADDR']."\t";

                            /* UA */
                            $errmsg.= $_SERVER['HTTP_USER_AGENT']."\t";

                            /* Error Code */
                            $errmsg.= $code."\t";

                            /* Error Message */
                            $errmsg.= $message."\t";

                            /* Error PayLoad */
                            $errmsg.= "\t".$payload;

                            self::write_to_log("error", $errmsg);
                            return true;
                        }
                        
                    }
                }
                return false;
            }
            
            /* Timestamp */
            $errmsg = date('Y-m-d H:i:s');
            
            /* Process */
            $errmsg.= "\t[{$application}]\t";
            
            /* IP */
            $errmsg.= $_SERVER['REMOTE_ADDR']."\t";
            
            /* UA */
            $errmsg.= $_SERVER['HTTP_USER_AGENT']."\t";
            
            /* Error Code */
            $errmsg.= $code."\t";
            
            /* Error Message */
            $errmsg.= $message."\t";
            
            /* Error PayLoad */
            $errmsg.= "\t".(string)$payload;
            
            self::write_to_log("error", $errmsg);
            
            return true;
        }
        
        
        
        
        /**
         * Log an audit/debug event
         * 
         * @static
         * @since 1.0.1
         * @param string $event Title for the event e.g. "Search Query"
         * @param string $data Detailed event information
         * @param string $application The name of your module e.g. "Search API"
         */
        public static function log_event($event = 'Event',$data = '[no data]',$application = 'argent_powered_app'){
            
            /* Timestamp */
            $logstring = date('Y-m-d H:i:s');

            /* Process */
            $logstring.= "\t[{$application}]\t";

            /* IP */
            $logstring.= $_SERVER['REMOTE_ADDR']."\t";

            /* UA */
            $logstring.= $_SERVER['HTTP_USER_AGENT']."\t";

            /* Event Name */
            $logstring.= $event."\t";

            /* Event Data */
            $logstring.= "\t".$data;
            
            self::write_to_log('event', $logstring);
            
        }
    }
}