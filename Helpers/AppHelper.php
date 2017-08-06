<?php

namespace Helpers;

class AppHelper {
	
	/**
	 * Split the string by lines
	 * 
	 * @param String $string
	 * @return Array
	 */
	public static function splitByLine( $string ){

		$vector = preg_split('/$\R?^/m', $string);

		return $vector;

	}

}