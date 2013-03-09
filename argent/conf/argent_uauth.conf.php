<?php
/**
 * User Authentication Configuration
 * 
 * 
 * @package Argent CloudKit
 * @version 1.2.0
 * @since 1.0.1
 * @author Nick Cousins <me@nickcousins.co.uk>
 * @link http://www.argentcloudkit.com/documentation 
 */


/**
 * Password Complexity 
 * 
 * Thanks to vaibhav http://www.techpint.com/programming/regular-expression-check-password-strength
 */

/* High Complexity - At least 8 uppercase, lowercase, numbers, specials */
define('UA_PASSWORD_COMPLEXITY','/^.*(?=.{8,})(?=.*[A-Z])(?=.*[\d])(?=.*[\W]).*$/');
define('UA_PASSWORD_COMPLEXITY_ERROR','Password must be at least 8 characters long and contain uppercase and lowercase letters, digits and special characters');


/* Medium Complexity - At least 8 uppercase, lowercase, numbers */
// define('UA_PASSWORD_COMPLEXITY','/^.*(?=.{8,})(?=.*[A-Z])(?=.*[\d]).*$/');
// define('UA_PASSWORD_COMPLEXITY_ERROR','Password must be at least 8 characters long and contain uppercase and lowercase letters and digits');


/* Low Complexity - At least 8 uppercase, lowercase */
// define('UA_PASSWORD_COMPLEXITY','/^.*(?=.{8,})(?=.*[A-Z]).*$/');
// define('UA_PASSWORD_COMPLEXITY_ERROR','Password must be at least 8 characters long and contain uppercase and lowercase letters');


/**
 * New Account Timeout
 * 
 * After how many hours should an unverified new account be removed 
 */
define('NEW_ACCOUNT_TIMEOUT','24');



define('SESSION_SALT','uJ;~va`ws-N`p*A.nQmH2 }^9aZf8*+U+!&o-}t?Yl!4 q+zV.U2n%m0{1t0.o5t');



define('SESSION_NAME','ua');



define('SESSION_TIMEOUT',900);


/* Permission Definition Constants */
define ('AG_PERMISSION_CREATE','create');
define ('AG_PERMISSION_READ','read');
define ('AG_PERMISSION_UPDATE','update');
define ('AG_PERMISSION_DELETE','delete');