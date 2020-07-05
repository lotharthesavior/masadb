<?php

namespace Models\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * The purpose of this code is to organize the Workflow tasks
 * of Git in the Data Storage.
 *
 * @author Savio Resende <savio@savioresende.com.br>
 */
trait GitWorkflow
{
    /**
     * 
     */
    protected function checkGitUser() 
    {
        // TODO: get from config
        $this->git->setGitConfig('user.email', 'savio@savioresende.com.br');
        $this->git->setGitConfig('user.name', 'Savio Resende');
        $user_email = $this->git->getGitConfig('user.email');
        
        if (empty($user_email)) {
            throw new \Exception('User not set for Git environment.');
        }
    }

    /**
     * Central place to save the version
     * 
     * @internal it happens asyncronously when the request is not the 
     *           first commit of the repository
     * 
     * @internal if happens syncronously when the request is the first
     *           commit of the repository
     */
    public function saveVersion()
    {
        $this->checkGitUser();

        $result_stage = $this->git->stageChanges();
        $result_commit = $this->git->commitChanges();

        // get the filesystem for the current database
        $local_address = $this->config['database-address'] . '/' . $this->_getDatabaseLocation();
        $filesystem = $this->filesystem->getFileSystemAbstraction($local_address);

        $this->git->placeMetadata($this->database, $filesystem);

        // $this->updateCache();

        return $result_stage && $result_commit;
    }

    /**
     * This method save records version in the cache.
     *
     * @param int $item
     * @param bool $is_delete - is marks if the operation is to remove the item
     */
    public function saveRecordVersion($item = null, bool $is_delete = false)
    {
        // get the filesystem for the current database
        $local_address = $this->config['database-address'] . '/' . $this->_getDatabaseLocation();
        $filesystem = $this->filesystem->getFileSystemAbstraction($local_address);

        $this->saveVersion();

        // Update cache by loading it and placing the new record
        // if ($this->getNoCache() === false) {
        //     if ($is_delete) {
        //         $this->removeItemFromCache($item);
        //     } else {
        //         if ($filesystem->has($item)) {
        //             $this->removeItemFromCache($item);
        //         }
        //         $this->addItemToCache($item);
        //     }
        // }

        return true;
    }

}