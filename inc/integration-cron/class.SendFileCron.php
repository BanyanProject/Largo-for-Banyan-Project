<?php

class SendFileCron extends IntegrationCron {

	protected $dbtable = 'frm_file';

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
		
		$nbapi = new NationBuilderAPI;
		
		foreach($this->list as $rec) {
			
			// Person Record
			
			$person = $this->makePerson($rec);
			$res = $api->put('/api/v1/people/push',array('person' => $person));
			
			if (isset($res['code']))
			{
				$this->errors++; 
				$this->log .= $rec['email']."\tpeople/push\terror ".$res['code']."\n";
				continue;										
			} 
				
			$this->log .= $rec['email']."\tpeople/push\tsuccess\n";
			$person = $res['person'];
			$id = $res['person']['id'];
			
			unset($res);
			
			// Tags
			
			$tags = array('form-send-file');
						
			if ($rec['email_signup']) {
				$tags[] = 'form-email';
				$this->emailNewsletterSignup($rec, $person);	
			}
		
			$this->tags($tags, $rec, $person);
		
			unset($person, $tags);	
		}
	}
	
}

?>