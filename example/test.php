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

// get available categories
$categories = $flattr->getCategories();

// Get available languages
$languages = $flattr->getLanguages();

// get clicks
$clicks = $flattr->getClicks('201010');
?>

<p>
	You are authenticated as <?php echo $me['username'] ?>.
</p>

<p>
	You've made the following clicks <br />
	<?php 
		foreach( $clicks as $key => $click )
		{
			printf( '%s <a href="%s">%s</a><br />', date('Y-m-d', $click['click_time']), $click['thing']['url'], $click['thing']['title'] );
		}
	?>
</p>


<p>
	You have <?php echo count($myThings) ?> things.
</p>
<?php foreach ( $myThings as $thing ): ?>
<p>
	Title: <?php echo $thing['title'] ?>
</p>
<?php endforeach ?>

<h2> Submit a thing </h2>
<form method="post" action="submit_thing.php">
	<div>
		<input type="text" name="url" value="the url">
	</div>
	<div>
		<input type="text" name="title" value="the title">
	</div>
	<div>
		<textarea name="description">description</textarea>
	</div>
	<div>
		<select name="language">
			<?php foreach($languages as $language) : ?>
			<option value="<?=$language['id']?>"><?=$language['name']?></option>
			<?php endforeach?>
		</select>
	</div>
	<div>
		<select name="category">
			<?php foreach($categories as $category) : ?>
			<option value="<?=$category['id']?>"><?=$category['name']?></option>
			<?php endforeach?>
		</select>
	</div>

	<div>
		<input type="text" name="tags" value="comma,seperated,taglist"/>
	</div>
	<input type="submit">
</form>
