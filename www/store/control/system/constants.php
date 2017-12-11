<?php

//-------------------------------------------------------------
// CONSTANTS
// Edit values on right, DO NOT change values in capitals
//-------------------------------------------------------------

define('SCRIPT_VERSION', '3.3');
define('SCRIPT_NAME', 'Maian Cart');
define('SCRIPT_URL', 'maiancart.com');
define('SCRIPT_ID', 11);

define('GLOBAL_PATH', substr(dirname(__file__), 0, strpos(dirname(__file__), 'control')-1) . '/');
define('MSW_PHP', (version_compare(PHP_VERSION, '7.1.0', '<') ? 'old' : 'new'));
define('AUTO_FILL_PATH', dirname(__file__));

?>