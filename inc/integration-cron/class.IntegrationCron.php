<?php

require_once('class.NameParser.php');

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

	protected $dbtable = 'frm_integration_cron';
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

	abstract protected function buildList();

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
		$wpdb->insert($this->dbtable,$data);
	}

	protected function getLastCronTimestamp()
	{
		global $wpdb;		
		$querystr = "select max(timestamp) from {$this->dbtable} where cron = %s";
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
	
		if (defined('NB_PARENT_ID'))
			$person['parent_id'] = NB_PARENT_ID;	
	
		$fields = array('first_name','last_name');
		
		foreach ($fields as $f) {
			if (isset($rec[$f]) && !empty($rec[$f]))
				$person[$f] = $rec[$f];
		}

		return $person;		
	}

	
}

?>