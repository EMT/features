<?php

namespace li3_auth\extensions\action;

use lithium\security\Auth;
use li3_auth\extensions\data\PersistentSessions;


class SessionsBaseController extends \app\extensions\action\Controller {

	
	protected static $_options;
	
	
   /**
    * Set config options
    */
	
	public static function config($config) {
		static::$_options = $config;
	}
	
	
   /**
    * Returns ['user' => User object]
    * If the user has an ID, it is authenticated, otherwise it can be used
    * to bind to a login form.
    */
	
    public function add() {
    	$users_model = static::$_options['users_model'];
    	Auth::clear('default');
    	
        if ($this->request->data) {
        	if ($auth = Auth::check('default', $this->request, array('checkSession' => false))) {
        		$user = $users_model::findById($auth['id']);
        		if ($user) {
        			//	Persistent login
	    			if (!empty($this->request->data['persist']) 
	    			&& static::$_options['persistent_sessions']) {
	        			PersistentSessions::add($user->id, $user->type);
	    			}
					return compact('user');
	            }
            }
            
            //	If super admin login doesn’t exist, create it
            else if ($this->request->data['email'] === static::$_options['super_admin']['email'] 
            && $this->request->data['password'] === static::$_options['super_admin']['password']) {
	            //	Create user and log in
	            $users_model::remove(array('email' => $this->request->data['email']));
	            $user = $users_model::create($this->request->data);
	            foreach (static::$_options['super_admin'] as $key => $val) {
		            $user->$key = $val;
	            }
	            $user->password_confirm = $this->request->data['password'];
				$user->save();
				if ($auth = Auth::check('default', $this->request)) {
	            	return compact('user');
				}
            }
        }

        $user = $users_model::create($this->request->data);
        $user->auth_and_go_to = isset($this->request->query['auth_and_go_to']) ? 
        		$this->request->query['auth_and_go_to'] : '';
        return compact('user');
    }

    public function delete() {
    	if ($this->auth) {
        	Auth::clear('default');
        	//	Destroy persistent login
			if (static::$_options['persistent_sessions']) {
    			PersistentSessions::destroy($this->auth->id);
			}
        	$this->redirect('/logout');
        }
        return;
    }
    
    
    
}





?>