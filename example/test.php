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

// Did we succeed?
if ( $flattr->error() )
{
    die( 'Error ' . $flattr->error() );
}

// Get user info for the authenticated user
$me = $flattr->getUserInfo();

// Get authenticated users things
$myThings = $flattr->getThingList();

?>
<p>
	You are authenticated as <?php echo $me['username'] ?>.
</p>
<p>
	You have <?php echo count($myThings) ?> things.
</p>
<?php foreach ( $myThings as $thing ): ?>
<p>
	Title: <?php echo $thing['title'] ?>
</p>
<?php endforeach ?>
