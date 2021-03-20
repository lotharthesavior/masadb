<?php

namespace Models\Interfaces;

interface GenericInterface
{
    /**
     * Set the database
     *
     * @param string $database
     * @return void
     */
    public function setDatabase(string $database);

    /**
     * Get the database
     *
     * @return string
     */
    public function getDatabase();

    /**
     * Set the Client ID
     *
     * @param string $client_id
     * @return void
     */
    public function setClientId(string $client_id);

    /**
     * Get the Client ID
     *
     * @return string
     */
    public function getClientId();

}
