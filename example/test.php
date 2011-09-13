<?php

require_once('header.php');

// Get user info for the authenticated user
$me = $flattr->getUserInfo();

// Get authenticated users things
$myThings = $flattr->getThingList();

if ( count($myThings) > 0 )
{
    $thingId = $myThings[0]['id'];
    $oneThing = $flattr->getThing( $thingId ); //Note that getThingList returns complete things so this call is unnecessary but provided here for reference.
}

// get available categories
$categories = $flattr->getCategories();

// Get available languages
$languages = $flattr->getLanguages();


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
        
        <?php if (isset($oneThing)): ?>
        <p>
        	This is one of your things: <br />
        	<p style="margin-left: 10px;">
        	Title: <?php echo $oneThing['title'], '<br />'; ?>
        	Url: <?php echo $oneThing['url'], '<br />'; ?>
        	Tags:
        	<?php
        	    if (!empty($oneThing['tags']) && is_array($oneThing['tags']))
        	    {
        	        echo implode(', ', $oneThing['tags']);
        	    }
        	?>
        	<br />
        	Description: <br />
        	<?php echo nl2br( $oneThing['story'] ); ?>
        	</p>
        </p>
        <?php endif; ?>
        
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
<?php require_once('footer.php')?>
