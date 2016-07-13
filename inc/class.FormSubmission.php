<?php

require_once('class.Validator.php');
require_once('class.MandrillMessage.php');

if (!defined('SUBMISSION_TABLE'))
	define('SUBMISSION_TABLE','frm_submission');

if (!defined('FORM_TIME_LIMIT'))
	define('FORM_TIME_LIMIT', 600);

if (!defined('DEFAULT_FROM_EMAIL'))
	define('DEFAULT_FROM_EMAIL', 'admin@banyanproject.coop');

if (!defined('DEFAULT_FROM_NAME'))
	define('DEFAULT_FROM_NAME', 'Banyan Project');

if (!defined('DEFAULT_TO_EMAIL'))
	define('DEFAULT_TO_EMAIL', 'admin@banyanproject.coop');

if (!defined('DEFAULT_TO_NAME'))
	define('DEFAULT_TO_NAME', 'Banyan Project');


abstract class FormSubmission {
	
	protected $dbtable;			
	
	protected $strict = false;
	protected $fields = array();
	protected $input = array();	
	protected $output = array();

	protected $requireToken = false;
	protected $loadTimestamp;
	
	protected $validationErrors = array();
	protected $errorFields = array();
	
	protected $adminMsgTemplate;
	protected $userMsgTemplate;
	
	public function __construct()  {
	
		if (is_array($_POST))
			$this->input = $_POST;
		else {
			$this->validationErrors[] = "Your submission was blank.";	
		}
							
		if (isset($_SERVER['REMOTE_ADDR']))		
			$this->output[SUBMISSION_TABLE]['ip_address'] = $_SERVER['REMOTE_ADDR'];
		
		if (isset($_SERVER['HTTP_USER_AGENT']))
			$this->output[SUBMISSION_TABLE]['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

		$this->output[SUBMISSION_TABLE]['submit_timestamp'] = time();
		
		$this->output[SUBMISSION_TABLE]['form'] = get_class($this);
		
		if (wp_get_session_token())
			$this->output[SUBMISSION_TABLE]['session_token'] = wp_get_session_token();
		
		if (is_user_logged_in()){
			
			$this->output[SUBMISSION_TABLE]['user_id'] = get_current_user_id();
		
			if (defined('NB_SLUG'))
				$this->output[SUBMISSION_TABLE]['nationbuilder_slug'] = NB_SLUG;
		
			$this->output[SUBMISSION_TABLE]['nationbuilder_id'] = nb_get_user_meta(get_current_user_id(), 'id');
			//$this->output[SUBMISSION_TABLE]['is_member'] = nb_is_member();
		}
		
		// google analytics fields
		if (isset($_COOKIE['__utma']))
			$this->output[SUBMISSION_TABLE]['utma'] = $_COOKIE['__utma'];
		
		if (isset($_COOKIE['__utmb']))
			$this->output[SUBMISSION_TABLE]['utmb'] = $_COOKIE['__utmb'];
		
		if (isset($_COOKIE['__utmz']))
			$this->output[SUBMISSION_TABLE]['utmz'] = $_COOKIE['__utmz'];
		
		if (isset($_COOKIE['___utmv']))
			$this->output[SUBMISSION_TABLE]['utmv'] = $_COOKIE['___utmv'];
		
		if (isset($_COOKIE['___utmx']))
			$this->output[SUBMISSION_TABLE]['utmx'] = $_COOKIE['___utmx'];	
			
	}

	public function requiresToken() {
		return $this->requireToken;
	}
	
	public function checkToken() {
				
		if (!$this->requireToken) 
			return true;
		
		if (!isset($this->input['load_timestamp']) || $this->input['load_timestamp'] == NULL || $this->input['load_timestamp'] == '') {
			$this->validationErrors[] = 'An unknown error occured.';
			return false;
		}
		
		if (!isset($_COOKIE['bp_salt'])) {
			$this->validationErrors[] = 'An unknown error occured.';
			return false;
		}
				
		if ($_COOKIE['bp_salt'] != md5($this->input['load_timestamp'].AUTH_SALT)) {
			$this->validationErrors[] = 'An unknown error occured.';
			return false;
		}
		
		if ($this->input['load_timestamp'] < mktime() - FORM_TIME_LIMIT) {
			$this->validationErrors[] = "Your time limit expired.  Please refresh the form and try again.";
			return false;
		}	
		
		return true;			
	}	
	
	public function validate($field,$filter,$constraints=NULL,$error_msg=NULL) {
		
		// 1. check if field and filter are 'required'
		
		if ($filter == 'required') {
			
			if (!isset($this->input[$field]) || $this->input[$field] == NULL) {
				$this->validationErrors[] = ucfirst(str_replace('_',' ',$field)) . " is required.";
				$this->errorFields[] = $field;
			}			
		}
					
		// 2. if filter is not 'required', skip validation if field is blank
		
		else {
			
			if (!isset($this->input[$field]) || $this->input[$field] == NULL) {
				
				if (isset($this->input[$field]))
					unset($this->input[$field]);
				
				return true;
			}
			// 3. validate not null input via the validator class

			else {

				$v = $this->input[$field];
				
				switch ($filter) {
					
					case 'date' :
						
						if (is_array($constraints))
							$res = $this->validator()->date($v,$constraints);
						else
							$res = $this->validator()->date($v);
						break;
						
					case 'between' :
						
						if (!is_array($constraints) || !isset($constraints['min']) || !isset($constraints['max'])) {
							$this->validationErrors[] = "Please notify a system administrator. " . ucfirst(str_replace('_',' ',$field)) . " must be assigned minimum and maximum values.";
							$this->errorFields[] = $field;
							break;
						}
					
						$res = $this->validator()->between($v,$constraints['min'],$constraints['max']);			
						break;
				
					case 'greaterThan' :
					
						if (is_array($constraints)) {
							
							if (isset($constraints['min'])) 
								$min = $constraints['min'];
							else {
								$this->validationErrors[] = "Please notify a system administrator. " . ucfirst(str_replace('_',' ',$field)) . " must be assigned a minimum value.";
								$this->errorFields[] = $field;
								break;
							}
						}
						elseif (!is_numeric($constraints)) {
							$this->validationErrors[] = "Please notify a system administrator. " . ucfirst(str_replace('_',' ',$field)) . " must be assigned a minimum value.";
							$this->errorFields[] = $field;
							break;
						}
						else {
							$min = $constraints;
						}
						
						$res = $this->validator()->greaterThan($v,$min);			
						break;
		
					case 'lessThan' :
					
						if (is_array($constraints)) {
							
							if (isset($constraints['max']) && is_numeric($constraints['max'])) 
								$max = $constraints['max'];
							else {
								$this->validationErrors[] = "Please notify a system administrator. " . ucfirst(str_replace('_',' ',$field)) . " must be assigned a maximum value.";
								$this->errorFields[] = $field;
								break;
							}
						}
						elseif (!is_numeric($constraints)) {
							$this->validationErrors[] = "Please notify a system administrator. " . ucfirst(str_replace('_',' ',$field)) . " must be assigned a maximum value.";
							$this->errorFields[] = $field;
							break;
						}
						else {
							$max = $constraints;
						}
						
						$res = $this->validator()->lessThan($v,$max);			
						break;
		
					case 'regex' :
						
						if (is_array($constraints)) {
							
							if (isset($constraints['pattern']) && is_string($constraints['pattern'])) 
								$pattern = $constraints['pattern'];
							else {
								$this->validationErrors[] = "Please notify a system administrator. " . ucfirst(str_replace('_',' ',$field)) . " must be assigned a matching pattern.";
								$this->errorFields[] = $field;
								break;
							}
						} 
						elseif (!is_string($constraints['pattern'])) {
							$this->validationErrors[] = "Please notify a system administrator. " . ucfirst(str_replace('_',' ',$field)) . " must be assigned a matching pattern.";
							$this->errorFields[] = $field;
							break;
						}
						else {
							$pattern = $constraints;
						}
			
						$res = $this->validator()->regex($v,$pattern);
						break;
			
					case 'stringLength' :
						
						if (is_array($constraints)) {
										
							if (!isset($constraints['minlength']) && !isset($constraints['maxlength'])) {
								$this->validationErrors[] = "Please notify a system administrator. " . ucfirst(str_replace('_',' ',$field)) . " must be assigned a mininum or maximum string length.";
								$this->errorFields[] = $field;
								break;
							}		
							
							if (isset($constraints['minlength']) && !is_numeric($constraints['minlength'])) {
								$this->validationErrors[] = "Please notify a system administrator. " . ucfirst(str_replace('_',' ',$field)) . " must be assigned a valid minimum length.";
								$this->errorFields[] = $field;
								break;
							}
									
							if (isset($constraints['maxlength']) && !is_numeric($constraints['maxlength'])) {
								$this->validationErrors[] = "Please notify a system administrator. " . ucfirst(str_replace('_',' ',$field)) . " must be assigned a valid maximum length.";
								$this->errorFields[] = $field;
								break;
							}
							
							if (!isset($constraints['minlength']))
								$constraints['minlength'] = NULL;
							
							if (!isset($constraints['maxlength']))
								$constraints['maxlength'] = NULL;			
														
						} else {
							$this->validationErrors[] = "Please notify a system administrator. " . ucfirst(str_replace('_',' ',$field)) . " must be assigned a mininum or maximum string length.";
							$this->errorFields[] = $field;
							break;
						}
						
						$res = $this->validator()->stringLength($v,$constraints['minlength'],$constraints['maxlength']);
						break;
					
					case 'allowedValues' :
						
						if (!is_array($constraints)) {
							$this->validationErrors[] = "Please notify a system administrator. " . ucfirst(str_replace('_',' ',$field)) . " requires that allowed values be set.";
							$this->errorFields[] = $field;
						}
						
						$res = $this->validator()->allowedValues($v,$constraints);
						break;
					
					case 'alpha' :
					case 'alphaNumeric' :
					case 'alphaNumericWhite' :
					case 'alphaWhite' :
					
						// don't validate blanks
						if ($v == NULL)
							$res = true;
						else
							$res = $this->validator()->$filter($v);
						
						break;
					
					default :
					
						$res = $this->validator()->$filter($v);
						break;
				}
			}
		} 
				
		// 4. return true on successful validations, return false and set error messages for unsuccessful validations
			
		if (!isset($res))
			return false;
		
		if (isset($res) && is_array($res)) {
					
			if (!is_null($error_msg)) 
				$this->validationErrors[] = $error_msg;
			else {		
				foreach($res as $error) {
					$this->validationErrors[] = ucwords(str_replace('_',' ',$field)) . ": " . $error;
				}	
			}
			$this->errorFields[] = $field;
			return false;	
		}
				
		if ($res)
			return true;				
	}
	
	public function setError($msg) {
		$this->validationErrors[] = $msg;	
	}

	public function isValid() {
		if (count($this->validationErrors) > 0)
			return FALSE;
		else 
			return TRUE;
	}

	public function transform() {
		$this->removeBlankInputs();
		$this->transformInput();
		$this->setValidationFlags();				
	}

	public function removeBlankInputs() {
		
		foreach ($this->input as $f => $v) {
			if ($v == NULL)
				unset($this->input[$f]);
		}
	}

	public function transformInput() {
		
		
		foreach ($this->input as $f => $v) {
			
			switch ($f) {
				
				// fields that are not saved
				case 'submitted' :
				case 'token' :
				case 'submit' :
				case 'action' :
				case 'term_id' :
					
					break; 
				
				// form fields saved to submission table
				case 'load_timestamp' : 
					
					if (!in_array($f,$this->errorFields))
						$this->output[SUBMISSION_TABLE][$f] = $v;
					
					break;				
				
				// all other form fields
				default :
					
					if (!in_array($f,$this->errorFields))
						$this->output[$this->dbtable][$f] = $v;
					
					break;
			}
		}
	}

	public function setValidationFlags() {
		
		if (count($this->validationErrors) > 0)	{
			$this->output[SUBMISSION_TABLE]['validate'] = 0;
			$this->output[SUBMISSION_TABLE]['validate_error_msg'] = implode("\n",$this->validationErrors);		
		} else 
			$this->output[SUBMISSION_TABLE]['validate'] = 1;		
	}

	public function setMandrillFlags() {
		
		foreach (array('admin' => 'adminMsg', 'user' => 'userMsg') as $person => $m) {
		
			$res = $this->$m()->getResponse();
			
			// set success flag
			$field = "mandrill_{$person}";
			
			if (in_array($res['status'],array('sent','queued','scheduled')))
				$this->output[SUBMISSION_TABLE][$field] = 1;
			else
				$this->output[SUBMISSION_TABLE][$field] = 0;
			
			// set other fields
			foreach ($res as $k => $v) {
				
				if (in_array($k,array('status','reject_reason','exception','message','_id')) && $v != NULL) {
					
					$field = "mandrill_{$person}_{$k}";
					$this->output[SUBMISSION_TABLE][$field] = $v;
				}	
			}
		}	
	}
			
	public function persist() {
			
		global $wpdb;		
		$id = NUlL;
		
		foreach ($this->output as $table => $fields) {
					
			if ($table != SUBMISSION_TABLE && $id != NULL)
				$fields['id'] = $id;	
				
			$res = $wpdb->insert($table,$fields);
						
			if ($table == SUBMISSION_TABLE)
				$id = $wpdb->insert_id;
		}
	}

	public function formatErrorMsgHtml() {
		$html = "<ul>\n\t<li>";
		$html .= implode("</li>\n\t<li>",$this->validationErrors);
		$html .= "</li>\n</ul>\n";
		return $html;
	}

	public function outputValue($field) 
	{
		if (isset($this->output[SUBMISSION_TABLE][$field]))
			return $this->output[SUBMISSION_TABLE][$field];
		
		if (isset($this->output[$this->dbtable][$field]))
			return $this->output[$this->dbtable][$field];
		
		return NULL;
	}

	public function getValidationErrors() {
		return $this->validationErrors;
	}

	protected function validator() {
		
		static $validator;
		
		if (!isset($validator)) {
			$validator = new Validator;
		}
		
		return $validator;
	}
	
	public function adminMsg($template=NULL) {
		
		if (is_null($template)) 
			$template = $this->adminMsgTemplate;
		
		static $adminMsg;
			
		if (!isset($adminMsg)) {
			$adminMsg = new MandrillMessage($template);
		}
			
		return $adminMsg;
	}
		
	public function userMsg($template=NULL) {
		
		if (is_null($template)) 
			$template = $this->userMsgTemplate;
		
		static $userMsg;
			
		if (!isset($userMsg)) {
			$userMsg = new MandrillMessage($template);
		}
			
		return $userMsg;
	}

}

?>