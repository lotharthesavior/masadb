<?php

namespace Models\Interfaces;

interface BagInterface
{

	/**
	 * Define location for bag
	 * 
	 * @internal the verified method 'createBagForRecord' is from 'BagUtilities' Trait
	 * @param Integer $id
	 * @param bool $is_bag
	 * @return String
	 */
	public function locationOfBag( $id, $is_bag );
	
}