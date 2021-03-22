<?php

namespace Models\Interfaces;

use Ds\Deque;
use Models\Interfaces\FileSystemInterface;

interface GitInterface
{
    // keep the Git instance for interactions
    // protected $repo;

    /**
     * Prepare the repository for the job
     *
     * @param string $database_address
     *
     * @return void
     * @internal this method is necessary because the instance is
     *           created before the address is available. This is
     *           happens for the possibility of Polymorphism.
     */
    public function setRepo(string $database_address);

    /**
     * @param string $database
     * @param FileSystemInterface $file_system
     * @param bool $is_bag
     * @param string $database_address
     *
     * @return Deque
     */
    public function lsTreeHead(
        string $database,
        FileSystemInterface $file_system,
        bool $is_bag,
        string $database_address
    ): Deque;

    /**
     * Turn the git ls-tree command into Array with
     * discriminated metadata
     *
     * @param string $cli_result
     * @param Bool $is_db - here is decided if the parsing will fill id
     *                      attribute or not
     * @param \Models\Interfaces\FileSystemInterface $file_system
     * @param bool $is_bag
     * @param string $database_address
     *
     * @return \Ds\Deque
     * @internal the $cli_result param "row" is expected to be like this:
     *               structure1: "100644 blob 0672e3d1ca4498ea4f6de663764e28f712468b03	oauth/access_token/1.json"
     *
     */
    public function parseLsTree(string $cli_result, bool $is_db = false, \Models\Interfaces\FileSystemInterface $file_system, bool $is_bag, string $database_address);

    /**
     * Wrapper for git show command
     *
     * @param string $file
     * @param string $branch
     *
     * @return string - command line result
     */
    public function showFile(string $file, string $branch = "master");

    /**
     * Execute git cli add
     *
     * @param string|null $item
     *
     * @return bool
     */
    public function stageChanges($item = null): bool;

    /**
     * Execute git cli commit
     *
     * @return bool
     */
    public function commitChanges(): bool;

    /**
     * Sets the record type in the returning collection.
     *
     * @param string $class
     *
     * @return void
     */
    public function setDataObject(string $class): void;
}
