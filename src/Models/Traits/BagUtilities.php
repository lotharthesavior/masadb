<?php

namespace Models\Traits;

use \Lotharthesavior\BagItPHP\BagIt;

trait BagUtilities
{

    /**
     * @param Integer $id
     * @return String path of the record
     */
    protected function createBagForRecord($id)
    {

        $record_path = $this->config['database-address'] . '/' . $this->_getDatabaseLocation() . '/' . $id;

        $bag = new BagIt($record_path);

        $bag->addFile($record_path . '.json', $id . '.json');

        $bag->update();

        unlink($record_path . '.json');

        return $id;

    }

}
