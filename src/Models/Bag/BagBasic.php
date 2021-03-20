<?php

namespace Models\Bag;

class BagBasic implements \Models\Interfaces\BagInterface
{
    /**
     * Define location for bag
     *
     * @param Integer $id
     * @param bool $is_bag
     * @return String
     * @internal the verified method 'createBagForRecord' is from 'BagUtilities' Trait
     */
    public function locationOfBag($id, $is_bag)
    {
        $location = '/' . $id;

        if ($is_bag) {

            $location = '/' . $id . '/data/' . $id;

        }

        return $location;
    }

}
