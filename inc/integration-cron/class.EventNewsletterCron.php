<?php

class EventNewsletterCron extends IntegrationCron {

	protected $dbtable = 'frm_event_newsletter';

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
			$id = $res['person']['id'];
			
			unset($res);
			
			// Tags
			
			$tags = array('web-email');
						
			$this->eventNewsletterSignup($rec, $person);	
		
			$this->tags($tags, $rec, $person);
		
			unset($person, $tags);	
		}
	}
	
	protected function eventNewsletterSignup($rec, $person) {

		$id = $person['id'];

			$res = $this->nbapi()->post("/api/v1/lists/". EVENT_NEWSLETTER_LIST_ID . "/people", array('people_ids'=> array($id)));
	
			if (isset($res['code']))
			{
				$this->errors++; 
				$this->log .= $rec['email']."\tlists/". EVENT_NEWSLETTER_LIST_ID . "/people\terror ".$res['code']."\n";
			} else {						
				$this->log .= $rec['email']."\tlists/". EVENT_NEWSLETTER_LIST_ID . "/people\tsuccess\n";
			}

		return $res;	
	}
	
}

?>