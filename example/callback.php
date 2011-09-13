<?php

session_start();

// Our configuration, edit it to set your API credentials
require_once('config.php');

// Flattr REST library
require_once('flattr_rest.php');

function saveToken($token, $secret)
{
	// You should store this in a more persistent way
	$_SESSION['flattr_access_token'] = $token;
	$_SESSION['flattr_access_token_secret'] = $secret;
}

// Setup a new client
$flattr = new Flattr_Rest(APP_KEY, APP_SECRET, $_SESSION['flattr_request_token']['oauth_token'], $_SESSION['flattr_request_token']['oauth_token_secret']);

if ( ! isset($_GET['oauth_verifier']) || empty($_GET['oauth_verifier']) )
{
	die('Authentication was unsuccessful');
}

// Get an access token
$access_token = $flattr->getAccessToken($_REQUEST['oauth_verifier']);

// Did we succeed?
if ( $flattr->error() )
{
    die( 'Error ' . $flattr->error() );
}

echo "saveToken(".var_export($access_token,true).");<br/>\n";
// Save the access token, it will be valid until the user revokes it.
saveToken($access_token['oauth_token'], $access_token['oauth_token_secret']);

// Now it's time to make some REST calls

?>
		<a href="test.php">Run some REST calls</a> 
<?php require_once('footer.php'); ?>
