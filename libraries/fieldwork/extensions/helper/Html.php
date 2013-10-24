<?php

namespace fieldwork\extensions\helper;

class Html extends \lithium\template\helper\Html {

	public function paras($string) {
		return str_replace("\n", "</p>\n<p>", '<p>' . $string . '</p>');
	}

}

?>