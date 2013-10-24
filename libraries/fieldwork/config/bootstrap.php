<?php


//	Set up Access rules

use fieldwork\access\Access;

Access::setBehaviours(array(
	'default' => array(
		'authenticated' => function() {throw new \Exception('You don’t have access to this page.', 403); },
		'unauthenticated' => function() {
			header('Location: /login?auth_and_go_to=' . urlencode($_SERVER["REQUEST_URI"]), true, 302);
			exit();
		}
	)
));

Access::setRules([
	'is_super_admin' => ['sad' => true],
	'is_admin' => [
		'sad' => true,
		'adm' => true
	],
	'is_supervisor' => ['sup' => true],
	'is_owner' => [
		['id' => ':user_id']
	],
	'is_me' => [
		['id' => ':id']
	],
	'is_on_project' => [
		function($user, $project) {
			foreach ($project->users_projects as $up) {
				if ($up->user_id === $user->id) {
					return true;
				}
			}
			return false;
		}
	],
	'is_project_member' => [
		function($user, $project) {
			foreach ($project->users_projects as $up) {
				if ($up->user_id === $user->id && $up->relationship === 'mem') {
					return true;
				}
			}
			return false;
		}
	],
	'is_project_supervisor' => [
		function($user, $project) {
			foreach ($project->users_projects as $up) {
				if ($up->user_id === $user->id && $up->relationship === 'sup') {
					return true;
				}
			}
			return false;
		}
	],
	'is_project_owner' => [
		function($user, $project) {
			foreach ($project->users_projects as $up) {
				if ($up->user_id === $user->id && $up->owner) {
					return true;
				}
			}
			return false;
		}
	],
	'is_thread_member' => [
		function($user, $com) {
			if ($user->id === $com->user_id) {
				return true;
			}
			foreach ($com->users_coms as $uc) {
				if ($uc->user_id === $user->id) {
					return true;
				}
			}
			return false;
		}
	],
	'can_observe' => [
		'sad' => true,
		'adm' => true, 
		'obs' => true
	]
]);

?>