<?php

class Validator {
	
	public function allowedValues($text,$allowed) {
		
		if (in_array($text,$allowed))
			return true;
		else 
			return array('"{$text}" is not a valid entry.');
	}
	
	public function alpha($text)
	{
		require_once('Zend/Validate/Alpha.php');
		$v = new Zend_Validate_Alpha();
		
		if ($v->isValid($text))
			return true;
		else 
			return $v->getMessages();			
	}

	// alphanumerics
	public function alphaNumeric($text)
	{
		require_once('Zend/Validate/Alnum.php');
		$v = new Zend_Validate_Alnum();
		
		if ($v->isValid($text))
			return true;
		else 
			return $v->getMessages();
			
	}

	// alpha and whitespace
	public function alphaWhite($text)
	{
		require_once('Zend/Validate/Alpha.php');
		$v = new Zend_Validate_Alpha(TRUE);
		
		if ($v->isValid($text))
			return true;
		else 
			return $v->getMessages();		
	}

	// alphanumerics and whitespace
	public function alphaNumericWhite($text)
	{
		require_once('Zend/Validate/Alnum.php');
		$v = new Zend_Validate_Alnum(TRUE);
		
		if ($v->isValid($text))
			return true;
		else 
			return $v->getMessages();
	}

	// between
	public function between($text,$min,$max)
	{
		require_once('Zend/Validate/Between.php');		
		$v = new Zend_Validate_Between($min,$max);
		
		if ($v->isValid($text))
			return true;
		else 
			return $v->getMessages();	
	}

	// boolean
	public function boolean($text)
	{
		require_once('Zend/Validate/InArray.php');		
		$v = new Zend_Validate_InArray(array('0','1'));
		
		if ($v->isValid($text))
			return true;
		else 
			return $v->getMessages();	
	}

	// date
	public function date($text,$options=array())
	{		
		require_once('Zend/Validate/Date.php');
		$v = new Zend_Validate_Date($options);
		
		if ($v->isValid($text))
			return true;
		else 
			return $v->getMessages();	
	}

	// digits
	public function digits($text)
	{
		require_once('Zend/Validate/Digits.php');		
		$v = new Zend_Validate_Digits();
		
		if ($v->isValid($text))
			return true;
		else 
			return $v->getMessages();	
	}

	// email
	public function email($text)
	{
		require_once('Zend/Validate/EmailAddress.php');
		$v = new Zend_Validate_EmailAddress();
		
		if ($v->isValid($text))
			return true;
		else 
			return $v->getMessages();
	}

	// float
	public function float($text)
	{
		$text = (float) $text;
		
		require_once('Zend/Validate/Float.php');
		$v = new Zend_Validate_Float();
		
		if ($v->isValid($text))
			return true;
		else 
			return $v->getMessages();	
	}
	
	// greater than
	public function greaterThan($text,$min)
	{
		require_once('Zend/Validate/GreaterThan.php');
		$v = new Zend_Validate_GreaterThan($min);
		
		if ($v->isValid($text))
			return true;
		else 
			return $v->getMessages();		
	}

	// hex
	public function hex($text)
	{
		require_once('Zend/Validate/Hex.php');
		$v = new Zend_Validate_Hex();
		
		if ($v->isValid($text))
			return true;
		else 
			return $v->getMessages();		
	}

	// int
	public function int($text)
	{
		require_once('Zend/Validate/Int.php');
		$v = new Zend_Validate_Int(array('locale' => 'en'));
		
		if ($v->isValid($text))
			return true;
		else 
			return $v->getMessages();		
	}
		
	// less than
	public function lessThan($text,$max)
	{
		require_once('Zend/Validate/LessThan.php');
		$v = new Zend_Validate_LessThan($max);
		
		if ($v->isValid($text))
			return true;
		else 
			return $v->getMessages();		
	}

	// not empty
	public function notEmpty($text)
	{
		require_once('Zend/Validate/NotEmpty.php');
		$v = new Zend_Validate_NotEmpty();
		
		if ($v->isValid($text))
			return true;
		else	
			return $v->getMessages();	
	}

	// regex
	public function creditCard($text,$cards)
	{
		$options = array(
			'type' => $cards
		);
		
		require_once('Zend/Validate/CreditCard.php');
		$v = new Zend_Validate_CreditCard($options);
		
		if ($v->isValid($text))
			return true;
		else 
			return $v->getMessages();		
	}


	// regex
	public function regex($text,$pattern)
	{
		require_once('Zend/Validate/Regex.php');
		$v = new Zend_Validate_Regex($pattern);
		
		if ($v->isValid($text))
			return true;
		else 
			return $v->getMessages();		
	}

	// string length
	public function stringLength($text,$min=NULL,$max=NULL)
	{
		$options = array();
			
		if (!is_null($min))
			$options['min'] = $min;
		
		if (!is_null($max))
			$options['max'] = $max;
				
		require_once('Zend/Validate/StringLength.php');
		$v = new Zend_Validate_StringLength($options);
		
		if ($v->isValid($text))
			return true;
		else 
			return $v->getMessages();	
	}
	
	public function url($text)
	{
		$pattern = '/(((http|ftp|https):\/{2})+(([0-9a-z_-]+\.)+(aero|asia|biz|cat|com|coop|edu|gov|info|int|jobs|mil|mobi|museum|name|net|org|pro|tel|travel|ac|ad|ae|af|ag|ai|al|am|an|ao|aq|ar|as|at|au|aw|ax|az|ba|bb|bd|be|bf|bg|bh|bi|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|cr|cu|cv|cx|cy|cz|cz|de|dj|dk|dm|do|dz|ec|ee|eg|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gg|gh|gi|gl|gm|gn|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|im|in|io|iq|ir|is|it|je|jm|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|me|mg|mh|mk|ml|mn|mn|mo|mp|mr|ms|mt|mu|mv|mw|mx|my|mz|na|nc|ne|nf|ng|ni|nl|no|np|nr|nu|nz|nom|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|ps|pt|pw|py|qa|re|ra|rs|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tf|tg|th|tj|tk|tl|tm|tn|to|tp|tr|tt|tv|tw|tz|ua|ug|uk|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw|arpa)(:[0-9]+)?((\/([~0-9a-zA-Z\#\+\%@\.\/_-]+))?(\?[0-9a-zA-Z\+\%@\/&\[\];=_-]+)?)?))\b/imuS';
		return $this->regex($text,$pattern);
	}

	public function visaMasterCard($text) 
	{
		require_once('Zend/Validate/CreditCard');
		$v = new Zend_Validate_CreditCard;
		$v->setType(
			array(
				Zend_Validate_CreditCard::VISA
				, Zend_Validate_CreditCard::MASTERCARD
			)
		);

		if ($v->isValid($text))
			return true;
		else 
			return $v->getMessages();	
	}

	public function visaMasterCardAmEx($text) 
	{
		require_once('Zend/Validate/CreditCard');
		$v = new Zend_Validate_CreditCard;
		$v->setType(
			array(
				Zend_Validate_CreditCard::VISA
				, Zend_Validate_CreditCard::MASTERCARD
				, Zend_Validate_CreditCard::AMERICAN_EXPRESS
			)
		);

		if ($v->isValid($text))
			return true;
		else 
			return $v->getMessages();	
	}

	public function visaMasterCardAmExDiscover($text) 
	{
		require_once('Zend/Validate/CreditCard');
		$v = new Zend_Validate_CreditCard;
		$v->setType(
			array(
				Zend_Validate_CreditCard::VISA
				, Zend_Validate_CreditCard::MASTERCARD
				, Zend_Validate_CreditCard::AMERICAN_EXPRESS
				, Zend_Validate_CreditCard::DISCOVER
			)
		);

		if ($v->isValid($text))
			return true;
		else 
			return $v->getMessages();	
	}

}

?>