<?php

$basePath = pathinfo(dirname(__FILE__));
define('PATH', substr($basePath['dirname'], 0, -9) . '/');

// ERROR REPORTING..
include(PATH . 'control/classes/class.errors.php');
if (ERR_HANDLER_ENABLED) {
  register_shutdown_function('mcFatalErr');
  set_error_handler('mcErrorhandler');
}

// SET PATH TO CART FOLDER..
define('PARENT', 1);

// DATABASE CONNECTION..
include(PATH . 'control/connect.php');
include(PATH . 'control/functions.php');

// INIT..
include(PATH . 'control/system/init.php');

// CHECK PAYMENT METHOD IS ENABLED..
if (!isset($mcSystemPaymentMethods['beanstream']['ID'])) {
  include(PATH . 'control/system/headers/200.php');
  exit;
}

// DETECT SSL..
$ssl = mc_detectSSLConnection($SETTINGS);
$url = ($ssl == 'yes' ? str_replace('http://', 'https://', $SETTINGS->ifolder) . '/' : $SETTINGS->ifolder . '/');

header("Location: " . $url . "index.php?p=declined" . (isset($_GET['errorMessage']) ? '&errorMessage=' . $_GET['errorMessage'] : ''));

?>