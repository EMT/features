<?php

namespace li3_auth\extensions\action;

use lithium\security\Auth;
use lithium\security\Password;


Auth::config([
 	'default' => [
 		'adapter' => 'Form',
 		'model' => 'Users',
 		'fields' => array('email', 'password'),
 		'validators' => [
 			'password' => function($form, $data) {
                return (trim($form)) && Password::check($form, $data);
            }
 		]
 	]
]);



SessionsBaseController::config([
	'users_model' => 'app\models\Users',
	'super_admin' => [
		'email' => 'andy@reallysimpleworkss.com', 
		'password' => 'andybdsa20131977',
		'fname' => 'Andy',
		'lname' => 'Gott',
		'role' => 'sad',
		'terms' => 1,
		'verified' => 1
	],
	'persistent_sessions' => true
]);
	
?>