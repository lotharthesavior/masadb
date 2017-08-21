<?php

namespace Models\Bag;

class BagBasic implements \Models\Interfaces\BagInterface
{
	/**
	 * Define location for bag
	 * 
	 * @internal the verified method 'createBagForRecord' is from 'BagUtilities' Trait
	 * @param Integer $id
	 * @param bool $is_bag
	 * @return String
	 */
	public function locationOfBag( $id, $is_bag ){
		$location = '/' . $id;

		if( $is_bag ){

			$location = '/' . $id . '/data/' . $id;

		}

		return $location;
	}

}