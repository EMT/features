<?php 


/**
 * Handles application-wide controller actions
 */


namespace app\extensions\action;

use app\models\Users;
use lithium\security\validation\RequestToken;
use lithium\core\Environment;
use lithium\security\Auth;
use lithium\storage\Session;
use fieldwork\access\Access;
use fieldwork\messages\Messages;



class Controller extends \lithium\action\Controller {

	
	
	//	The authenticated user
	public $auth;
	
	//	Is an admin action
/* 	public $admin = false; */


	protected function _init() {
		
		parent::_init();
		
		
		//	Set up authenticated user
		$this->auth = Auth::check('default');
		if ($this->auth) {
			$this->auth = Users::findById($this->auth['id']);
		}
		
        
        //	Secure forms
		$non_secured_actions = array('users/bounce');
		if ($this->request->data && !RequestToken::check($this->request) 
		&& !in_array($this->request->url, $non_secured_actions)) {
			//$host = $this->request->env('HTTP_HOST');
			//Logger::error("Possible CSRF attack from host $host");
			throw new \Exception('Invalid form token.', 403);
        }
        
        //	Secure json and csv formats to avoid making too much data public
        //	See the $formats public property in specific controllers for details
        $whitelisted_formats = array('html', 'part'); // Formats accessible for any page
        if ($this->request->type && !in_array($this->request->type, $whitelisted_formats)) {
        	$action = $this->request->action;
        	if (isset($this->request->admin) && $this->request->admin === 'admin') {
	        	$action = 'admin_' . $action;
	        }
        	$valid = false;
        	if (isset($this->formats) && isset($this->formats[$action])) {
	        	foreach ($this->formats[$action] as $k => $v) {
		        	if (is_int($k) && $v === $this->request->type) {
			        	$valid = true;
			        	break;
		        	}
		        	else if ($k === $this->request->type) {
			        	if (Access::check($this->auth, $v)) {
				        	$valid = true;
				        	break;
			        	}
		        	}
	        	}
        	}
	        if (!$valid) {
		        throw new \Exception('Not available in ' . $this->request->type . ' format.', 404);
	        }
        }	
        
        //	Set any message keys found in the URL query string
        if (isset($this->request->query['messages'])) {
	        Messages::add($this->request->query['messages']);
        }
        
        //	Set the $new key if found
        $new = (isset($this->request->query['new'])) ? $this->request->query['new'] : array();
        if (!is_array($new)) {
	        $new = explode(',', $new);
        }
        $this->new = function($value) use ($new) {
	        return in_array($value, $new);
        };
    
    }
	

	public function render(array $options = array()) {
		
        
        //	Make some contextual available to the view template
        //	Don’t do this for json or csv responses, as all data is outputted
        if (!$this->isDataRequest()) {
	        $this->set(array(
	        	'env' => Environment::get(),
	        	'build' => Environment::get('build'),
	        	'external_js' => (isset($this->external_js)) ? $this->external_js : array(),
	        	'auth' => $this->auth
	        ));
	        /*
if (!isset($options['data']['body_classes'])) {
		        $options['data']['body_classes'] = (isset($this->body_classes)) ? $this->body_classes : '';
	        }
*/
	        if (!isset($options['data']['body_data'])) {
		        $options['data']['body_data'] = (isset($this->body_data)) ? $this->body_data : array();
	        }
	        
	        if (empty($options['data']['fb_login_url'])) {
		        $options['data']['fb_login_url'] = Session::read('fb_login_url');
	        }
	    }
		
		parent::render($options);
 
    }
    
    
    
    public function isDataRequest() {
	    return (in_array($this->request->type, array('json', 'csv')));
    }
    
    
    


}
