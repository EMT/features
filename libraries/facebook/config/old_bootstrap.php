<?php



use \lithium\storage\Session;
use \lithium\security\Auth;
use \lithium\action\Dispatcher;
use \lithium\action\Response;
use \facebook\Facebook;
use \facebook\FacebookApiException;

require_once "../../libraries/facebook/Facebook.php";
/*
Session::config(array(
     'default' => array('adapter' => 'Php')
));
*/

Dispatcher::applyFilter('run', function($self, $params, $chain) {
			
     // Create our Application instance (replace this with your appId and secret).
     $facebook = new Facebook(array(
          'appId'  => '570776342981585',
          'secret' => 'aec18e54c4812840683b94948f80391d',
          'cookie' => true,
     ));
	
     $fbuser = $facebook->getUser();
	
     $me = null;
     // Session based API call.
/* var_dump($params); */
     if ($fbuser) {
          // Write the session
          Session::write('fb_session', $fbuser);
          try {
               $uid = $facebook->getUser();
               $me = $facebook->api('/me');
          } catch (FacebookApiException $e) {
               error_log($e);
          }
     }
	
     // login or logout url will be needed depending on current user state.
     if ($me) {
	  // This will come in handy later
	  Session::write('fb_logout_url', $facebook->getLogoutUrl());
	  	
	  // So set the Auth and pass along (in the session) the data from FB API
	  Auth::set('default', $me);
                
     } else {
	  // Again, this will come in handy (unless you're using the JavaScript SDK)
          Session::write('fb_login_url', $facebook->getLoginUrl());
	  
          // If no FB session, clear any local session we may have set
/* 	  Auth::clear('user'); */
     }
        
     
     return $chain->next($self, $params, $chain);
});


?>