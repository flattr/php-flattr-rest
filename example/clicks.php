<?php

require_once('header.php');

$clicks = $flattr->getClicks('201011'); // get clicks
?>

Listing clicks:

<p>
	<?php 
		if (is_array($clicks))
		{
			echo "You've flattred the following things: <br />";
			foreach( $clicks as $key => $click )
			{
				printf( '%s <a href="%s">%s</a><br />', date('Y-m-d', $click['click_time']), $click['thing']['url'], $click['thing']['title'] );
			}
		}
		else
		{
			echo "You haven't flattred anything yet. :(";
		}
	?>
</p>