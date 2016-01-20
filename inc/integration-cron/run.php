<?php

require_once('../../../../../wp-load.php');

require_once('class.IntegrationCron.php');
require_once('class.ContactCron.php');

echo("start contact cron\n");

$cron = new ContactCron;
$cron->run();
		
?>
