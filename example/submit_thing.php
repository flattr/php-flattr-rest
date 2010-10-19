<?php
session_start();

// Our configuration, edit it to set your API credentials
require_once('config.php');

// OAuth library
require_once('oauth.php');

// Flattr REST library
require_once('flattr_rest.php');

function loadToken()
{
	return array($_SESSION['flattr_access_token'], $_SESSION['flattr_access_token_secret']);
}

// Load token
list($token, $token_secret) = loadToken();

// Setup a new client
$flattr = new Flattr_Rest(APP_KEY, APP_SECRET, $token, $token_secret);
extract($_POST);
$flattr->submitThing($url, $title, $category, $description, $tags, $language);
