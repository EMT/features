<?php

namespace li3_auth\extensions\data;

use lithium\util\Validator;
use lithium\security\Password;
use app\models\Users;
use app\models\UserTokens;
use fieldwork\email\Email;


class UsersBase extends \app\extensions\data\Model {
    
    
    
   /**
    * Instance of Email for sending user emails
    */
    
    private static $_email;
    
    protected static function _email() {
	    if (!self::$_email) {
	    	self::$_email = new Email([
	    		'mandrill_api_key' => 'zlVDcAzsFLbqFh0rnMzsQA',
	    		'from_name' => 'Plan Do – The Social Action App',
	    		'from_email' => 'plandobeta@madebyfieldwork.com'
	    	]);
	    }
	    return self::$_email;
    }
    

	public function save($entity, $data = null, array $options = array()) {
	
		$options = $options + [
			'validate' => true
		];
	
		if ($data) {
	        $entity->set($data);
	        $data = array();
	    }
	    
	    $ent = $entity->export();
		
		//	Used by the checkPassword validator for checking auth on actions requiring a password
		$entity->old_password = (!empty($ent['data']['password'])) ? $ent['data']['password'] : null;
		
		//	VALIDATION: password validation must be done before hashing
    	if ($options['validate'] && !$entity->validates()) {
	    	return false;
    	}
		
		//	Hash password if new or updated
		if (!$entity->exists() 
	    || (isset($entity->password) && $entity->password !== $ent['data']['password'])) {
	    	$entity->password = Password::hash($entity->password);
	    }
	    	    
	    return parent::save($entity, $data, $options);
	}   
	
	
	public function fullName($entity) {
		return $entity->fname . ' ' . $entity->lname;
	} 
	
	
   /**
   	* Return the user’s email verification code
   	*/
   	
	public function verifyCode($entity) {
		return md5($entity->verifyString());
	}
	
	public function verifyString($entity) {
		return $entity->id . $entity->fname . $entity->lname . $entity->email . $entity->created;
	}
	
	
	public function verifyLink($entity) {
		$url = $pageURL = 'http';
		$url .= (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === 'on') ? 's' : '';
		$url .= '://' . $_SERVER["SERVER_NAME"];
		$url .= '/users/' . $entity->id . '/verify?c=' . $entity->verifyCode();
		return $url;
	}
	
	
   /**
   	* Verify the user’s email verification code
   	*/
   	
	public function checkVerifyCode($entity, $code) {
		return $code === $entity->verifyCode();
	}
	
	
	public function emailVerifyCode($entity) {
		$message = [
			'to' => [
				[
					'email' => $entity->email,
					'name' => $entity->fullName()
				]
			]
		];
		$template = 'user_verify';
		$data = [
			'fname' => $entity->fname,
			'verify_link' => $entity->verifyLink(),
			'email' => $entity->email
		];
		return self::_email()->sendTemplate($message, $template, $data);
	}
	
	
   /**
   	* Return the user’s password link
   	*/
   	
	public function passwordResetLink($entity) {
		$url = $pageURL = 'http';
		$url .= (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === 'on') ? 's' : '';
		$url .= '://' . $_SERVER["SERVER_NAME"];
		$url .= '/users/' . $entity->id . '/reset?c=' . rawurlencode(UserTokens::getNewToken($entity));
		return $url;
	}
	
	
	public function emailPasswordReset($entity) {
		$message = [
			'to' => [
				[
					'email' => $entity->email,
					'name' => $entity->fullName()
				]
			]
		];
		$template = 'password_reset';
		$data = [
			'fname' => $entity->fname,
			'reset_link' => $entity->passwordResetLink(),
			'email' => $entity->email
		];
		return self::_email()->sendTemplate($message, $template, $data);
	}
	
	
   /**
   	* Verify a password reset link against this user
   	*/
   	
	public function checkPasswordResetCode($entity, $code) {
		$token = UserTokens::findToken($entity->id);
		return ($token) ? $token->check($code) : false;
	}
	
	
   /**
   	* Expire a password reset token
   	*/
   	
	public function expirePasswordResetCode($entity) {
		$token = UserTokens::findToken($entity->id);
		return ($token) ? $token->delete() : false;
	}

}



/**
 * Checks against the both users and organisations tables and validates if the 
 * email address does not already exist
 */
Validator::add('uniqueEmail', function($value, $format, $options){
	return !Users::first(array('conditions' => array('email' => $value)));
});


/**
 * Checks against that the sumbitted password matches the existing password
 */
Validator::add('checkPassword', function($value, $format, $options){
   return Password::check($value, $options['values']['old_password']);
});



?>