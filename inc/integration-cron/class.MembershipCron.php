<?php

class MembershipCron extends IntegrationCron {

	protected $dbtable = 'frm_membership';

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
			
			// Step 1: Find/Update Person Record
			
			$person = $this->makePerson($rec);
			$res = $nbapi->put('/api/v1/people/push',array('person' => $person));
			
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

			// Step 2: Check for a Membership Record
			$membership = NULL;
			$memberships = $nbapi->get("/api/v1/people/{$id}/memberships");

			foreach ($memberships['result']['results'] as $m) {
				
				if ($m['name'] == $rec['type']) {
					$membership = $m;
					break;
				}
			} 	
							
			// Step 3: Create/Update Memberhsip Record

			$one_year = date("c", strtotime($rec['submit_timestamp']) + (365 * 24 * 60 * 60));	
			 			 
			if (is_null($membership)) {

				// create new membership
				
				$new = array(
					'name' => $rec['type']
					,'status' => 'active'
					,'started_at' => date("c",$rec['submit_timestamp'])
				);
								
				// non-recurring memberships expire in one year

				if ($rec['recurring'] == 'non-recurring')				
					$new['expires_on'] = $one_year;
				
				$res = $nbapi->post("/api/v1/people/{$id}/memberships",array("membership" => $new));

				if (isset($res['code']))
				{
					$this->errors++; 
					$this->log .= $rec['email']."\t/people/{$id}/memberships\terror ".$res['code']."\n";
					continue;										
				} 
					
				$this->log .= $rec['email']."\t/people/{$id}/memberships\tsuccess\n";
				$membership = $res['membership'];
								
			} else {
				
				// update membership
								
				$update = array(
					'name' => $rec['type']
					,'status' => 'active'
				);
								
				// expiration
				
				if ($row['recurring'] == 'non-recurring') {
					
					$extend = date("c",strtotime($membership['expires_on']) + (365 * 24 * 60 * 60));
						
					if (strtotime($extend) > strtotime($one_year))
						$update['expires_on'] = $extend;
					else
						$update['expires_on'] = $one_year;
						
				} else {
					
					if ($mbrsp['expires_on'] != NULL)
						$update['expires_on'] = date("c",strtotime($membership['expires_on']) + (100 * 365 * 24 * 60 * 60));
					else
						$update['expires_on'] = NULL;
				}			

				$res = $nbapi->put("/api/v1/people/{$id}/memberships",array("membership" => $update));

				if (isset($res['code']))
				{
					$this->errors++; 
					$this->log .= $rec['email']."\t/people/{$id}/memberships\terror ".$res['code']."\n";
					continue;										
				} 
					
				$this->log .= $rec['email']."\t/people/{$id}/memberships\tsuccess\n";
				$membership = $res['membership'];
				
			}
			
			unset($res);
			
			// Step 4: Create Donation Record
			
			$note = "For ". $membership['name'] ." Membership created at ". $membership['started_at'];
			$donation = $this->makeDonation($rec,$person, $note);
			$res = $nbapi->post('/api/v1/donations', array('donation' => $donation));
		
			if (isset($res['code']))
			{
				$this->errors++; 
				$this->log .= $rec['email']."\tdonations\terror ".$res['code']."\n";
				continue;										
			} 
				
			$this->log .= $rec['email']."\tdonations\tsuccess\n";
			
			unset($res);
			
			// Step 5: Tags and Email Signup
			
			$tags = array('form-membership');
			
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