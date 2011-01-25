<?php

set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');

// Your application key
define('APP_KEY', '--insert your application key here--');

// Your application secret
define('APP_SECRET', '--insert your application secret here--');

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
