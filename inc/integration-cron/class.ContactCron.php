<?php

class ContactCron extends IntegrationCron {

	protected $dbtable = 'frm_contact';

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
			
			// Tags
						
			$tags = array('web-contact');
						
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