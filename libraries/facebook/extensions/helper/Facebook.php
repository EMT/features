<?php

/**
 * Wrapper for using Access in Lithium views
 */

namespace facebook\extensions\helper;
use facebook\Fb as Fb;


class Facebook extends \lithium\template\Helper {

	public function loginUrl(array $params = array()) {
		return Fb::loginUrl($params);
	}

}


?>