<?php

class ContactCron extends IntegrationCron {

	protected function buildList()
	{
		$this->loadContactSubmissions();		
	}
	
	protected function loadContactSubmissions() {
		
		global $wpdb;
		
		$querystr = $wpdb->prepare('select * from frm_contact c join frm_submission s on c.id = s.id where s.submit_timestamp > %d and s.submit_timestamp <= %d', $this->start_timestamp, $this->end_timestamp);		
		$this->list = $wpdb->get_results($querystr, OBJECT);
	}	

	public function run() {
		
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
			
			$tags = array('form-contact');
			
			switch ($rec['type']) {
				
				case 'advertisting' :
					$tags[] = 'form-contact-advertising';
					break;
					
				case 'news-topic' :
					$tags[] = 'form-contact-news-topic';
					break;
					
				case 'editor' :
					$tags[] = 'form-contact-editor';
					break;					
			}
			
			if ($rec['email_signup']) 
				$tags[] = 'form-email';
			
			foreach ($tags as $tag) {
				
				$tagging = array(
					'tag' => $tag
				);
				
				$res = $api->put("/api/v1/people/{$id}/taggings",array('tagging' => $tagging));
				
				if (isset($res['code']))
				{
					$this->errors++; 
					$this->log .= $rec['email']."\tpeople/{$id}/taggings\terror ".$res['code']."\n";
				} else {
					$this->log .= $rec['email']."\tpeople/{$id}/taggings\tsuccess\n";
				}

				unset($res);
				unset($tagging);
			}
			
			// Email Newsletter
			
			if ($rec['email_signup'])
			{		
				$res = $api->post("/api/v1/lists/". EMAIL_NEWSLETTER_LIST_ID . "/people", array('people_ids'=> array($id)));
	
				if (isset($res['code']))
				{
					$this->errors++; 
					$this->log .= $rec['email']."\tlists/". EMAIL_NEWSLETTER_LIST_ID . "/people\terror ".$res['code']."\n";
				} else {						
					$this->log .= $rec['email']."\tlists/". EMAIL_NEWSLETTER_LIST_ID . "/people\tsuccess\n";
				}
			}

			$this->success++;
			unset($person);	
			unset($id);
		}
	}
	
}

?>