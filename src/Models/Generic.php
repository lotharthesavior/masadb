<?php

namespace Models;

use Git\Git;
use Git\GitRepo;
use Models\Exceptions\NotExistentDatabaseException;
use Models\Abstraction\GitDAO;
use Models\Interfaces\GenericInterface;
use Models\Traits\GitWorkflow;
use Models\Traits\BagUtilities;

/**
 * Class for Generic Model.
 *
 * The CRUD can be found at Abstraction/GitDAO.php
 */
class Generic extends GitDAO implements GenericInterface
{
    use GitWorkflow, BagUtilities;

    /** @var GitRepo */
    protected $repo;

    /** @var string */
    protected $database = '';

    /**
     * Set the database
     *
     * @param string $database
     *
     * @return void
     */
    public function setDatabase(string $database): void
    {
        $this->database = $database;

        $database_physical_location = $this->config['database-address'] . DIRECTORY_SEPARATOR . $this->_getDatabaseLocation();

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
    public function getDatabase(): string
    {
        return $this->database;
    }

    /**
     * Set the Client ID
     *
     * @param string $client_id
     *
     * @return void
     */
    public function setClientId(string $client_id): void
    {
        $this->client_id = $client_id;
    }

    /**
     * Get the Client ID
     *
     * @return string
     */
    public function getClientId(): string
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
    public function createDatabase(string $database): void
    {
        $this->database = $database;

        if (!$this->filesystem->createDatabaseDirectory($this->config['database-address'], $this->_getDatabaseLocation())) {
            throw new Exception("Database couldn't be created!");
        }

        $this->git->initRepository($this->config['database-address'] . DIRECTORY_SEPARATOR . $this->_getDatabaseLocation());
    }

}
