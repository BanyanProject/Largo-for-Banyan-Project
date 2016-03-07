<?php

require_once('OAuth2/Client.php');

class NationbuilderAPI {

	public function get($query,$params=array()) {
		return $this->oauth()->fetch(NB_BASE_API . $query, $params);	
	}

	public function post($query,$params) {

		$handle = curl_init();
				
		curl_setopt($handle, CURLOPT_URL, NB_BASE_API.$query."?access_token=".NB_ACCESS_TOKEN);
		curl_setopt($handle, CURLOPT_CUSTOMREQUEST,'POST');
		curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($params));
		curl_setopt($handle, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Accept: application/json'));  
		curl_setopt($handle, CURLOPT_RETURNTRANSFER,true);

		return json_decode(curl_exec($handle),true);
	}

	public function put($query,$params) {

		$handle = curl_init();
				
		curl_setopt($handle, CURLOPT_URL, NB_BASE_API.$query."?access_token=".NB_ACCESS_TOKEN);
		curl_setopt($handle, CURLOPT_CUSTOMREQUEST,'PUT');
		curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($params));
		curl_setopt($handle, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Accept: application/json'));  
		curl_setopt($handle, CURLOPT_RETURNTRANSFER,true);

		return json_decode(curl_exec($handle),true);		 
	}

	public function delete($query, $params=NULL) {

		$handle = curl_init();
				
		curl_setopt($handle, CURLOPT_URL, NB_BASE_API.$query."?access_token=".NB_ACCESS_TOKEN);
		curl_setopt($handle, CURLOPT_CUSTOMREQUEST,'DELETE');
		
		// DELETE action may not take params
		if (is_array($params))
			curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($params));
		
		curl_setopt($handle, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Accept: application/json'));  
		curl_setopt($handle, CURLOPT_RETURNTRANSFER,true);

		return json_decode(curl_exec($handle),true);		 
	}
	
	protected function oauth()
	{				
		static $oauth;
		
		if (!isset($oauth))
		{
			$oauth = new OAuth2\Client(NB_CLIENT_ID, NB_CLIENT_SECRET);
			$oauth->setAccessToken(NB_ACCESS_TOKEN);
		}
		
		return $oauth;				
	}
}

?>