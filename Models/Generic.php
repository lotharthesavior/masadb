<?php

namespace Models;

use \Git\Coyl\Git;
use \Models\Exceptions\NotExistentDatabaseException;

/**
 * Class for Generic Model.
 *
 * The CRUD can be found at Abstraction/GitDAO.php
 *
 * @author Savio Resende <savio@savioresende.com.br>
 */
class Generic extends \Models\Abstraction\GitDAO implements \Models\Interfaces\GenericInterface
{
    use Traits\GitWorkflow;

    // add this to make the GitModel knows where to find the record
    use Traits\BagUtilities;

    protected $repo;

    protected $database = '';

    /**
     * Set the database
     *
     * @param String $database
     * @return void
     */
    public function setDatabase(string $database)
    {
        $this->database = $database;

        $database_physical_location = $this->config['database-address'] . "/" . $this->_getDatabaseLocation();

        if (!file_exists($database_physical_location)) {
            throw new NotExistentDatabaseException("Database Doesn't Exist.");
        }

        if (isset($this->git)) {
            try {
                $this->git->setRepo($database_physical_location);
            } catch (GitException $e) {
                throw new Exception($e->getMessage());
            }
        }
    }

    /**
     * Get the database
     *
     * @return string
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * Set the Client ID
     *
     * @param string $client_id
     * @return void
     */
    public function setClientId(string $client_id)
    {
        $this->client_id = $client_id;
    }

    /**
     * Get the Client ID
     *
     * @return string
     */
    public function getClientId()
    {
        return $this->client_id;
    }

    /**
     * Create a database
     *
     * @param string $database
     *
     * @return void
     */
    public function createDatabase(string $database)
    {
        $this->database = $database;

        if (!$this->filesystem->createDatabaseDirectory($this->config['database-address'], $this->_getDatabaseLocation())) {
            throw new Exception("Database couldn't be created!");
        }

        $this->git->initRepository($this->config['database-address'] . '/' . $this->_getDatabaseLocation());
    }

}
