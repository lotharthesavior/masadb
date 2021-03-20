<?php

namespace Models\Interfaces;

interface BagInterface
{

    /**
     * Define location for bag
     *
     * @param Integer $id
     * @param bool $is_bag
     * @return String
     * @internal the verified method 'createBagForRecord' is from 'BagUtilities' Trait
     */
    public function locationOfBag($id, $is_bag);

}
