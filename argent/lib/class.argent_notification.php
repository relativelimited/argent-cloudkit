<?php
/**
 * Notification Class
 * 
 * 
 * @package Argent CloudKit
 * @subpackage argent_notification
 * @version 1.0
 * @since 1.1.0
 * @author Nick Cousins <me@nickcousins.co.uk>
 * @link http://www.argentcloudkit.com/documentation 
 */

if (!class_exists('argent_notification'))
{
    /* Include PHPMailer */
    
    if (file_exists(PHP_MAILER_PATH.'/class.phpmailer.php'))
        require_once(PHP_MAILER_PATH.'/class.phpmailer.php');
        
    class argent_notification{       
        
        
        
        
        /**
         * Send an email notification to a user
         * 
         * @static
         * @since 1.1.0
         * @param string $user_id User object_id
         * @param string $subject Email subject line
         * @param string $message Email body
         */
        public static function notify_user($user_id, $subject, $message){
            
            $error  =   new argent_error();
            
            if (!class_exists('PHPMailer'))
                $error->add('1042','PHPMailer is not available',NULL,'argent_notification');
            
            if ($error->has_errors())
                return $error;
            
            $mail =   new PHPMailer();

            $mail->AddReplyTo(NOTIFICATION_FROM_MAIL,NOTIFICATION_FROM_NAME);

            $mail->SetFrom(NOTIFICATION_FROM_MAIL,NOTIFICATION_FROM_NAME);

            $user_data  =   argent_uauth::user_get_data($user_id);
            
            if (argent_error::check($user_data))
            {
                return $user_data;
            }
            
            $mail->AddAddress($user_data['email'], $user_data['display_name']);

            $mail->Subject    = $subject;

            $mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test

            $mail->MsgHTML($message);

            if(!$mail->Send()) {
                $error->add('1040','Error sending mail',$mail->ErrorInfo,'argent_notification');
                return $error;
            } else {
                return 'Mail sent to '.$user_data['email'];
            }
        }
        
        
        
        
        /**
         * Merge fields in a specified template
         * 
         * @since 1.1.0
         * @static
         * 
         * @param string $template_file
         * @param array $merge_fields
         * @return string|\argent_error 
         */
        public static function merge_template($template_file=NULL,$merge_fields=array()){
            $error  =   new argent_error();
            
            if (!file_exists($template_file))
                $error->add('1100','File does not exist',$template_file,'argent_notification');
            
            if (!is_array($merge_fields))
                $error->add('1041','Invalid merge fields specified',$merge_fields,'argent_notification');
            
            if ($error->has_errors())
                return $error;
            
            $template   = file_get_contents($template_file);
            
            if (!$template)
                $error->add('1101','File could not be read',$template,'argent_notification');
            
            if ($error->has_errors())
                return $error;
            
            if (count($merge_fields)>0)
                foreach($merge_fields as $field=>$value)
                {
                    $template   = str_replace("%%FIELD_".$field, $value, $template);
                }
            
            return $template;
        }
    }
}