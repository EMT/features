<?php


namespace facebook;


use \app\models\Users;
use \lithium\storage\Session;
use \lithium\security\Auth;
use \lithium\action\Dispatcher;
use \lithium\action\Response;
use \facebook\Facebook;
use \facebook\FacebookApiException;

require_once "../../libraries/facebook/Facebook.php";


class Fb {
	
	
	private static $_appId = '570776342981585';
    private static $_secret = 'aec18e54c4812840683b94948f80391d';
    
    private static $_facebook;
     
     
	public static function facebook() {
		if (!self::$_facebook) {
			self::$_facebook = new Facebook(array(
	          'appId'  => self::$_appId,
	          'secret' => self::$_secret,
	          'cookie' => true
			));
	    }
	    return self::$_facebook;
	}
	
	
	public static function loginUrl(array $params = array()) {
		$params = $params + ['scope' => 'email'];
		return self::facebook()->getLoginUrl($params);
	}
	
	
	public static function logoutUrl() {
		return self::facebook()->getLogoutUrl();
	}
	

   /**
    * Log in with facebook, and register a new user if not already registered
    */
	
	public static function loginWithSignup() {
		$facebook = self::facebook();
		$fbuser = $facebook->getUser();	
		
		$me = null;
		if ($fbuser) {
			// Write the session
			Session::write('fb_session', $fbuser);
			try {
				$me = $facebook->api('/me');
			} 
			catch (FacebookApiException $e) {
				throw $e;
			}
		}

		if ($me) {
			if ($auth = Users::findByFbUid($me['id'])) {
				//	User has a Plan.Do account and has already connected FB
				$auth->save([
					'fb_access_token' => $facebook->getAccessToken()
				]);
				Auth::set('default', $auth->data());
				return $auth;
			}
			if ($auth = Users::findByEmail($me['email'])) {
				//	User has a Plan.Do account but has not yet connected FB
				$auth->save([
					'fb_uid' => $me['id'],
					'fb_access_token' => $facebook->getAccessToken()
				]);
				Auth::set('default', $auth->data());
				return $auth;
			}
			else {
				//	Create user
				$user = Users::create();
				if ($user->save([
					'fname' => $me['first_name'],
					'lname' => $me['last_name'],
					'email' => $me['email'],
					'fb_uid' => $me['id'],
				], ['validate' => false])) {
					Auth::set('default', $user->data());
					return $user;
				}
				else {
					var_dump($user->data());
					var_dump($user->errors());
				}
			}
		} 
		else {
			
		}
	}
	
	
}




?>