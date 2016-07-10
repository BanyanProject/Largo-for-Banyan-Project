<?php

class VolunteerCron extends IntegrationCron {

	protected $dbtable = 'frm_volunteer';

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
			
			// Step 1: Find/Update Person Record
			
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
			
			// Step 2: Tags and Email Signup
			
			$tags = array('web-volunteer');
			
			if ($rec['skills_journalism'])
				$tags[] = 'web-skill-journalism';
			
			if ($rec['skills_organizing'])
				$tags[] = 'web-skill-organizing';
			
			if ($rec['skills_graphic_design'])
				$tags[] = 'web-skill-design';
			
			if ($rec['skills_photo'])
				$tags[] = 'web-skill-photography';
			
			if ($rec['skills_video'])
				$tags[] = 'web-skill-video';
			
			if ($rec['skills_web'])
				$tags[] = 'web-skill-web-developer';
			
			if ($rec['interests_write'])
				$tags[] = 'web-volunteer-write';
			
			if ($rec['interests_volunteer'])
				$tags[] = 'web-volunteer-office';
	
			if ($rec['interests_event'])
				$tags[] = 'web-volunteer-event';
			
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