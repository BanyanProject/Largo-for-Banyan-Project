<?php

class DonateCron extends IntegrationCron {

	protected $dbtable = 'frm_donate';

	public function run()
	{	
		if ($this->count > 0)
		{			
			// parse first ant last names on list
			$this->parseNames();
						
			// send names to email server
			if (USE_NATIONBUILDER) {
				$this->runNationBuilder();
			}
			
			// persist metrics				
			$this->persist();
		}
	}
	
	protected function runNationBuilder() {
				
		foreach($this->list as $rec) {
			
			// Person Record
			
			$person = $this->makePerson($rec);
			$res = $this->nbapi()->put('/api/v1/people/push',array('person' => $person));
			
			if (isset($res['code']))
			{
				$this->errors++; 
				$this->log .= $rec['email']."\tpeople/push\terror ".$res['code']."\n";
				continue;										
			} 
				
			$this->log .= $rec['email']."\tpeople/push\tsuccess\n";
			$person = $res['person'];
			
			unset($res);

			// Donate Record
			
			$donation = $this->makeDonation($rec,$person);
			$res = $this->nbapi()->post('/api/v1/donations', array('donation' => $donation));
		
			if (isset($res['code']))
			{
				$this->errors++; 
				$this->log .= $rec['email']."\tdonations\terror ".$res['code']."\n";
				continue;										
			} 
				
			$this->log .= $rec['email']."\tdonations\tsuccess\n";
			
			unset($res);
			
			// Tags and Email Signup
			
			$tags = array('web-donate');
						
			if ($rec['email_signup']) {
				$tags[] = 'web-email';
				$this->emailNewsletterSignup($rec, $person);	
			}
		
			$this->tags($tags, $rec, $person);
		
			unset($person, $tags);	
		}
	}

	
}

?>