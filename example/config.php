<?php

define( 'LOCAL_DEV_ENV', true );

set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');

// Your application key
define('APP_KEY', 'P2aBZngZFQfz0tiywUWkF5u198BBu6T9YTAN7HDCoI2e2bCoVozqkUhkTJLdFvdt');

// Your application secret
define('APP_SECRET', 'eE7bbBEp9wt2xWhibGzmqHw9g8dLtg5xK3zMu41urkmP7tX80nkhUGg0EkCYrxl1');

// Your callback url
define('CALLBACK_URL', 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['SCRIPT_NAME']).'/callback.php');

// use utf-8!
?>
<!DOCTYPE html !>
<html>
    <head>
        <meta http-equiv="Content-Type" value="text/html; charset=utf-8"/>
    </head>
    <body>
