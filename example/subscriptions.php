<?php
require_once('header.php');

$me = $flattr->getUserInfo();

$subscriptions = $flattr->getSubscriptions();


echo 'Hello ', $me['username'], '<br />';

if (!empty($subscriptions))
{
	echo 'You have the following subscriptions: <br />';
	echo '<table><tr><th style="text-align:left;">Title</th><th>Months left</th></tr>';
	foreach( $subscriptions as $subscription )
	{
		printf('<tr><td>%s</td><td style="text-align:center;">%s</td></tr>', $subscription['thing']['title'], $subscription['monthsleft'] );
	}
	echo '</table>';
}
else
{
	echo 'You have no subscriptions. :(';
}