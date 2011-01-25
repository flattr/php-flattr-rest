<?php

define( 'LOCAL_DEV_ENV', true );

set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');

// Your application key
define('APP_KEY', 'gsI6OcRKE5RqI9M9aZ7OZdpmeG1b0Bl6UT2zWJ9rFQHeQkdR9avZeKbih2k7uw4e');

// Your application secret
define('APP_SECRET', 'gWE2wDj2kR601aH0b3PfERnMKyJ4UE90rDSN72GhJD8BDGsFz8K3PYFpmfk7JkX1');

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
