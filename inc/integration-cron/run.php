<?php

require_once('../../../../../wp-load.php');

require_once('class.IntegrationCron.php');
require_once('class.ContactCron.php');
require_once('class.DonateCron.php');
require_once('class.EmailNewsletterCron.php');
require_once('class.FoundingMembershipPledgeCron.php');
require_once('class.MembershipCron.php');
require_once('class.VolunteerCron.php');

$contact = new ContactCron;
$contact->run();

$donate = new DonateCron;
$donate->run();

$email = new EmailNewsletterCron;
$email->run();

$founding = new FoundingMembershipPledgeCron;
$founding->run();

$member = new MembershipCron;
$member->run();

$volunteer = new VolunteerCron;
$volunteer->run();
		
?>
