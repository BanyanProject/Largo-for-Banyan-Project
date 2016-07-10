<?php

class NameParser {

	protected $fullname;

	protected $pieces;
	protected $count;

	protected $prefix = '';
	protected $fname = '';
	protected $mname = '';
	protected $lname = '';
	protected $suffix = '';
	

	public function __construct($fullname)
	{
		$this->fullname = $fullname;

		$this->pieces = explode(' ',$this->fullname);
		$this->count = count($this->pieces);
	
		$this->parse();	
	}

	public function getPrefix() {
		return $this->prefix;
	}
	
	public function getFirstName() {
		return $this->fname;	
	}
	
	public function getLastName() {
		return $this->lname;	
	}
	
	public function getSuffix() {
		return $this->suffix;	
	}
	
	protected function parse() {
			
		if ($this->count == 1) {
			$this->fname = $this->fullname;
		}  
		
		elseif ($this->count == 2) {
			$this->fname = $this->pieces[0];
			$this->lname = $this->pieces[1];
		}  
		
		elseif ($this->count > 2) {
			
			$p = $this->pieces;
			
			// prefix
			if (in_array($p[0],array('Dr','Dr.','Miss','Mr','Mr.','Mrs','Mrs.','Ms','Ms.')))
			{
				$this->prefix = $p[0];
				array_shift($p);
			}

			// suffix		
			$last = count($p) - 1;
						
			if (in_array($p[$last],array('Jr','Jr.','Sr','Sr.','III','IV','V')))
			{
				$this->suffix = $p[$last];
				array_pop($p);
							
			}

			$last = count($p) - 1;			
			$this->lname = $p[$last];
			$this->fname = $this->combine(0,$last,$p); 
		}
	}
	
	protected function combine($start,$finish,$arr=NULL) {

		if (!is_array($arr))
			$arr = $this->pieces;

		$res = $this->pieces[$start];

		for ($i = $start; $i < $finish ; $i++) {				
			if ($i > $start)
				$res .= ' ' . $this->pieces[$i];
		}
		
		return $res;
	}
}