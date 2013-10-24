<?php

use lithium\net\http\Router;
use lithium\core\Environment;


/* if (!Environment::is('production')) { */
	Router::connect('/console/migrate/{:lib}', ['controller' => 'WebConsole', 'action' => 'migrate']);
/* } */

?>