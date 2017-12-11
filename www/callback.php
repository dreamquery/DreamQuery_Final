<<?php // index.php

$userInfo = $auth0->getUser();

if (!$userInfo) {
    // We have no user info
    // redirect to Login
} else {
    // User is authenticated
    // Say hello to $userInfo['name']
    // print logout button
} ?>
