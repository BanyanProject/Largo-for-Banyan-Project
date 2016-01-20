<?php

abstract class CreditCardSubmission extends FormSubmission {

	public function transform() {
		parent::transform();

		// cc - only save last 4 digits
		$this->output[$this->dbtable]['cc'] = substr($this->input['cc'],-4);
	
	}
	
	public function isApproved() {
		
		return true;
		// TODO: turn on
		/*
		if ($this->outputValue('authorize') == true)
			return true;
		else 
			return false;
		 * 
		 */
	}	
}

?>