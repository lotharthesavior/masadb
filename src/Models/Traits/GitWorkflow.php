<?php

namespace Models\Traits;

use Exception;

/**
 * The purpose of this code is to organize the Workflow tasks
 * of Git in the Data Storage.
 */
trait GitWorkflow
{
    /**
     *
     */
    protected function checkGitUser()
    {
        global $config;

        $this->git->setGitConfig('user.email', $config['settings']['git_user_email'] ?? '');
        $this->git->setGitConfig('user.name', $config['settings']['git_user_name'] ?? '');
        $user_email = $this->git->getGitConfig('user.email');

        if (empty($user_email)) {
            throw new Exception('User not set for Git environment.');
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
    public function saveVersion(): bool
    {
        $this->checkGitUser();

        $result_stage = $this->git->stageChanges();
        $result_commit = $this->git->commitChanges();

        // get the filesystem for the current database
        $local_address = $this->config['database-address'] . '/' . $this->_getDatabaseLocation();
        $filesystem = $this->filesystem->getFileSystemAbstraction($local_address);

        $this->git->placeMetadata($this->database, $filesystem);

        // TODO: set this when cache solved.
        // $this->updateCache();

        return $result_stage && $result_commit;
    }

    /**
     * This method save records version in the cache.
     *
     * @param int $item
     * @param bool $is_delete - is marks if the operation is to remove the item
     *
     * @return bool
     */
    public function saveRecordVersion($item = null, bool $is_delete = false): bool
    {
        // get the filesystem for the current database
        $local_address = $this->config['database-address'] . '/' . $this->_getDatabaseLocation();
        $filesystem = $this->filesystem->getFileSystemAbstraction($local_address);

        $this->saveVersion();

        return true;
    }

}
