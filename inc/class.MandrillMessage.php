<?php

require_once('Mandrill.php');

class MandrillMessage
{
	protected $template;
	protected $content = array();
	protected $message = array();

	protected $domains = array();
	protected $track_opens = true;
	protected $track_clicks = true;
	
	protected $response;
	
	public function __construct($templ=NULL)
	{
		if (is_string($templ))
			$this->setTemplate($templ);
	
		$this->prepareMessage();	
	}

	public function send() {
		
		try {
			$res = $this->mandrill()->messages->sendTemplate($this->template,$this->content,$this->message);
		}
		catch (Exception $e) {
			
			$this->response = array(
				'status' => 'error'
				,'exception' => get_class($e)
				,'msg' => $e->getMessage()
			);
			
			return;
		}
				
		$this->response = $res[0];
		return;
	}

	public function getResponse() {
		return $this->response;
	}

	public function prepareMessage()
	{
		$this->message['track_opens'] = $this->track_opens;
		$this->message['track_clicks'] = $this->track_clicks;
		
		if (count($this->domains) > 0)
			$this->message['google_analytics_domains'] = $this->domains; 
	}

	public function setTemplate($templ) 
	{
		$this->template = $templ;
	}

	public function setContentMain($content) 
	{
		$this->content[] = array(
			'name' => 'main'
			, 'content' => $content
		);
	}

	public function setSubject($subject) 
	{
		$this->message['subject'] = $subject;
	}
	
	public function setFrom($name,$email) 
	{
		
		$this->message['from_name'] = $name;
		$this->message['from_email'] = $email;
		
	}

	public function setTo($name,$email) 
	{
		if (!isset($this->message['to']) || !is_array($this->message['to']))	
			$this->message['to'] = array();
		
		$this->message['to'][] = array(
			'name' => $name
			, 'email' => $email
		);	
	}

	public function setReplyTo($email) 
	{
		if (!isset($this->message['headers']) || !is_array($this->message['headers']))	
			$this->message['headers'] = array();
			
		$this->message['headers']['Reply-To'] = $email;
	}

	public function setVariable($name,$val) 
	{
		if (!isset($this->message['global_merge_vars']) || !is_array($this->message['global_merge_vars']))	
			$this->message['global_merge_vars'] = array();
			
		$this->message['global_merge_vars'][] = array(
			'name' => $name
			, 'content' => $val
		);
	}

	protected function mandrill()
	{				
		static $mandrill;
		
		if (!isset($mandrill))
		{
			$mandrill = new Mandrill(MANDRILL_API_KEY);				
		}
		
		return $mandrill;					
	}

}

?>