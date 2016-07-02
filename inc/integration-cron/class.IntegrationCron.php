<?php

require_once('../class.NameParser.php');

abstract class IntegrationCron {

	protected $start_timestamp;	
	protected $end_timestamp;
	
	protected $list;
	
	protected $count;
	protected $success = 0;
	protected $errors = 0;
	protected $log = '';
	protected $execution_time;
	protected $memory_usage;

	protected $dbtable;
	protected $subtable = 'frm_integration_cron';
	protected $fields = array('cron','timestamp','count','success','errors','log','execution_time','memory_usage');
	
	public function __construct() {
	
		// echo("called Cron::_construct() \n");
			
		// set timestamp	
		$this->end_timestamp = time();
		
		// get data on last cron
		$this->start_timestamp = $this->getLastCronTimestamp();
		
		// echo('start: '.$this->start_timestamp.'; end: '.$this->end_timestamp."\n");
		
		// assemble list
		$this->buildList();
				
		$this->count = count($this->list);
					
	}

	protected function buildList()
	{
		$this->loadSubmissions();		
	}
	
	protected function loadSubmissions()
	{	
		global $wpdb;
		
		$querystr = $wpdb->prepare("select t.*, s.* from {$this->dbtable} t join frm_submission s on t.id = s.id where s.submit_timestamp > %d and s.submit_timestamp <= %d and validate = 1", $this->start_timestamp, $this->end_timestamp);		
		$this->list = $wpdb->get_results($querystr, OBJECT);
	}	

	abstract public function run();

	protected function persist() {
		
		$dat = getrusage();
		$this->execution_time = $dat["ru_utime.tv_sec"] * 1000000 + $dat["ru_utime.tv_usec"];
		
		$this->memory_usage = memory_get_peak_usage();
		
		$data = array();		
		foreach($this->fields as $f) {
			if ($f == 'cron')
				$data[$f] = get_class($this);
			elseif ($f == 'timestamp')
				$data[$f] = $this->end_timestamp;
			else {
				if ($this->$f != NULL)	
					$data[$f] = $this->$f;
			}
		}

		global $wpdb;
		$wpdb->insert($this->subtable,$data);
	}

	protected function getLastCronTimestamp()
	{
		global $wpdb;		
		$querystr = "select max(timestamp) from {$this->subtable} where cron = %s";
		$res = $wpdb->get_var($wpdb->prepare($querystr,get_class($this)));
		
		if (!is_numeric($res))
			$res = 0;
		
		return $res;		
	}	
	
	protected function parseNames() {
		
		foreach ($this->list as $key => $row) {
			
			if (!isset($row->first_name) && isset($row->full_name))
			{
				$parser = new NameParser($row->full_name);
				$row->first_name = $parser->getFirstName();
				$row->last_name = $parser->getLastName();
				
				if ($parser->getSuffix() != NULL)
					$row->last_name .= ' ' . $parser->getSuffix();
					
				unset($parser);
			}
		}
	}

	protected function makePerson($rec) {
		
		if (!is_array($rec) || !isset($rec['email']) || empty($rec['email'])) {
			throw new BadMethodCallException;	
		}
		
		$person = array(
			'email' => $rec['email']
		);
	
		// non-standard fields
	
		if (defined('NB_PARENT_ID'))
			$person['parent_id'] = NB_PARENT_ID;	

		if (isset($rec['phone']))
			$person['phone'] = $this->parsePhone($rec['phone']);

		if (isset($rec['authorize_subscription_id']))
			$person['authorize_subscription_id'] = $rec['authorize_subscription_id'];
	
		// address fields
	
		$address_fields = array(
			'address' => 'address'
			,'city' => 'city'
			,'state_province' => 'state'
			,'country' => 'country_code'
			,'zip_postal' => 'zip'
		);	

		foreach ($address_fields as $f => $v) {
			if (isset($rec[$f]) && !empty($rec[$f]))
				$person['billing_address'][$v] = $rec[$f];
		}
	
		// standard fields
	
		$fields = array('first_name','last_name');
		
		foreach ($fields as $f) {
			if (isset($rec[$f]) && !empty($rec[$f]))
				$person[$f] = $rec[$f];
		}
	}

	protected function makeDonation($rec,$person,$note=NULL) {
			
		$donation = array(
			'billing_address' => array(
				'address1' => $rec['address']
				,'city' => $rec['city']
				,'country_code' => $rec['country']
				,'zip' => $rec['zip_postal']
			)
			,'created_at' => date("c",$rec['submit_timestamp'])
			,'donor_id' => $person['id']
			,'email' => $rec['email']
			,'payment_type_name' => 'Credit Card'			
			,'succeeded_at' => date("c",$rec['submit_timestamp'])
		);

		if (isset($rec['total']))
			$donation['amount_in_cents'] = round($rec['total'] * 100);
		elseif (isset($rec['amount']))
			$donation['amount_in_cents'] = round($rec['amount'] * 100);

		if (isset($row['state_province']) && $rec['state_province'] != NULL)
			$donation['billing_address']['state'] = $rec['state_province'];

		if (isset($rec['authorize_subscription_id']) && $rec['authorize_subscription_id'] != NULL)
			$donation['recurring_donation_id'] = $rec['authorize_subscription_id'];

		if (!is_null($note))
			$donation['note'] = $note;
		
		return $donation;
	}

	protected function tags($tags, $rec, $person) {

		$id = $person['id'];
	
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
		}
		
		return $res;
	}

	protected function emailNewsletterSignup($rec,$person) {

		$id = $person['id'];

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

		return $res;	
	}

	protected function parsePhone($phone) {
		$phone = preg_replace("/[^0-9]/", "", $phone);
 
		if(strlen($phone) == 7)
			return preg_replace("/([0-9]{3})([0-9]{4})/", "$1-$2", $phone);
		elseif(strlen($phone) == 10)
			return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "($1) $2-$3", $phone);
		else
			return $phone;
	}

}

?>