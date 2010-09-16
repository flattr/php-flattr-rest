<?php

session_start();

// Our configuration, edit it to set your API credentials
require_once('config.php');

// OAuth library
require_once('oauth.php');

// Flattr REST library
require_once('flattr_rest.php');

// Setup a new client
$flattr = new Flattr_Rest(APP_KEY, APP_SECRET);

// Get a request token
$token = $flattr->getRequestToken(CALLBACK_URL);

// Did we succeed?
if ( $flattr->error() )
{
    die( 'Error ' . $flattr->error() );
}

// Save the token in session for later use
$_SESSION['flattr_request_token'] = $token;

// Now, let's output an authentication url, param 1 is the request token and param 2 are the access scopes separated with ,
// After auth we will be redirected to callback.php
?>
 
<a href="<?php echo $flattr->getAuthorizeUrl($token, 'read,readextended,click,publish') ?>">Connect with Flattr</a>
