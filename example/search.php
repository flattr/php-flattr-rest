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

// Get the thing list, using a search query
if (isset($_GET['q']))
{
	$things = $flattr->getSearchThingList($_GET['q']);
}
else
{
	$things = array();
	echo 'ok didnt search for anything, listnening your things insteed';
	$things = $flattr->getThingList();
}

?>
<p>
	found <?php echo count($things) ?> things.
</p>
<?php foreach ( $things as $thing ): ?>
<p>
	Title: <?php echo $thing['title'] ?>
</p>
<?php endforeach ?>

<h2> Search for things</h2>
<form method="get" action="search.php">
	<div>
		<input type="text" name="q" value="the search query">
	</div>
	<div>
		<input type="submit">
	</div>
</form>
