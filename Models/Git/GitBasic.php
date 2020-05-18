<?php

namespace Models\Git;

use \Ds\Deque;

use Git\Git;
use Git\Console;
use Git\GitRepo;
use League\Flysystem\Filesystem;

use \Models\Interfaces\FileSystemInterface;

class GitBasic implements \Models\Interfaces\GitInterface
{
    /** @var GitRepo */
    protected $repo;

    /** @var Console */
    protected $console;

    /**
     * @param Git $repo
     */
    public function __constructor( $database_address = null )
    {
        $this->console = new Console;

        if( $database_address ) {
            $this->setRepo($database_address);
        }
    }

    /**
     * Prepare the repository for the job
     * 
     * @internal this method is necessary because the instance is 
     *           created before the address is available. This is
     *           happens for the possibility of Polymorphism.
     * 
     * @param string $database_address
     * 
     * @return void
     */
    public function setRepo( string $database_address )
    {
        if (!$this->console) {
            $this->console = new Console;
        }

        try {
            $this->repo = Git::open( $this->console, $database_address );
        } catch (GitException $e) {
            throw $e;
        }

        if ($this->isStatusDirty()) {
            $this->stageChanges();
            $this->commitChanges();
        }
    }

    /**
     * @internal depends on $this->repo
     * 
     * @param string $database  - format expected: "{string}/"
     * @param FileSystemInterface $filesystem
     * @param bool $is_bag
     * @param string $database_address
     * 
     * @return Deque
     */
    public function lsTreeHead( 
        string $database = '', 
        FileSystemInterface $filesystem, 
        bool $is_bag, 
        string $database_address
    ) {
        $this->checkRepo();

        $result = '';
        if (!$this->isEmptyRepository()) {
            $command = ' ls-tree HEAD ' . $database;

            $result = $this->console->runCommand(Git::getBin() . $command );
        }

        $is_db = $database != '';

        return $this->parseLsTree( $result, $is_db, $filesystem, $is_bag, $database_address );
    }

    /**
     * Turn the git ls-tree command into Array with
     * discriminated metadata
     * 
     * @internal the $cli_result param "row" is expected to be like this: 
     *               structure1: "100644 blob 0672e3d1ca4498ea4f6de663764e28f712468b03  oauth/access_token/1.json"
     * @param string $cli_result
     * @param bool $is_db - here is decided if the parsing will fill id 
     *                      attribute or not
     * @param FileSystemInterface $filesystem
     * @param bool $is_bag
     * @param string $database_address
     * 
     * @return Deque
     */
    public function parseLsTree( 
        string $cli_result, 
        bool $is_db = false, 
        FileSystemInterface $filesystem, 
        bool $is_bag, 
        string $database_address 
    ) {
        $result_array = \Helpers\AppHelper::splitByLine($cli_result);
        $result_array = array_filter($result_array);

        $result_deque = new Deque($result_array);

        $result_deque = $result_deque->map(function( $records_row ) use ($is_db, $filesystem, $is_bag, $database_address)
        {
            $new_record = new \Models\Record;
            $new_record->loadRowStructure1( $records_row, $is_db );
            $new_record = $filesystem->getFileContent( $new_record, $is_bag, $database_address );
            return $new_record;
        });

        return $result_deque;
    }

    /**
     * Wrapper for git show command
     * 
     * @internal depends on $this->repo
     * 
     * @param string $file
     * @param string $branch
     * 
     * @return String - command line result
     */
    public function showFile( string $file, string $branch = "master" )
    {
        $this->checkRepo();

        $result = $this->repo->show( $branch . ':' . $file );

        return $result;

    }

    /**
     * Check if the Repository is started.
     * 
     * @return void|throw
     */
    private function checkRepo()
    {
        if( !isset($this->repo) || empty($this->repo) )
            throw new \Exception("No Repository started.");
    }

    /**
     * Check if Repository is empty
     *
     * @return bool
     */
    private function isEmptyRepository()
    {
        $command = ' log -1';

        try {
            $this->console->runCommand(Git::getBin() . $command);
        } catch (\Exception $e) { // TODO: handle the error as a new type of exception: 'does not have any commits yet'
            $no_commits_yet = strstr($e->getMessage(), 'does not have any commits yet');
            return $no_commits_yet !== false;
        }

        return false;
    }

    /**
     * Execute git cli add
     * 
     * @todo analyze the result
     * 
     * @param string $item
     * 
     * @return bool
     */
    public function stageChanges(string $item = null)
    {
        if( !is_null($item) )
            $result = $this->repo->add($item);
        else
            $result = $this->repo->add();

        return true;
    }

    /**
     * Execute git cli commit
     * 
     * @todo analyze the result
     * 
     * @return bool
     */
    public function commitChanges(): bool
    {
        $message = "Commit from Masa - " . date("Y-d-m H:i:s") . ".";

        $this->repo->commit( $message );

        return true;
    }

    public function getStatus(): string
    {
        return $this->console->runCommand(Git::getBin() . ' status');
    }

    public function isStatusDirty(): bool
    {
        return strstr($this->getStatus(), 'Changes not staged for commit') !== false;
    }

    /**
     * Get the last version timestamp for cache purpose
     */
    public function getLastVersionTimestamp()
    {
        if ($this->isEmptyRepository()) {
            return 0;
        }

        return $this->repo->logFormatted("%at", "", "1");
    }

    /**
     * @internal for metadata spec, see @prepareMetadata method.
     */
    public function placeMetadata($database, Filesystem $filesystem)
    {
        $note_message = "";

        $metadata_json = $this->prepareMetadata($database, $filesystem);

        return $this->console->runCommand(Git::getBin() . " notes add -f -m '" . $metadata_json . "'");
    }

    /**
     * 
     */
    public function getMetadata()
    {
        try {
            $return = $this->console->runCommand(Git::getBin() . " notes show");
        } catch (\Exception $e) {
            $return = $e->getMessage();
        }

        return $return;
    }

    /**
     * Metadata:
     * 1. total number of records
     * 2. last ID
     */
    public function prepareMetadata($database, Filesystem $filesystem)
    {
        $current_metadata = $this->getMetadata();
        
        if( strpos($current_metadata, "error") != -1 )
            return $this->generateMetadata($database, $filesystem);

        return $current_metadata;
    }

    /**
     * @internal for metadata spec, see @prepareMetadata method.
     */
    public function generateMetadata($database, Filesystem $filesystem)
    {
        $metadata = new \stdClass;

        $filesystem_report = new Deque($filesystem->listContents("/"));

        $filesystem_report->sort(function($a, $b)
        {
            return (int) $a['filename'] > (int) $b['filename'];
        });

        $metadata->total_records = $filesystem_report->count();
        $metadata->last_id = ((object) $filesystem_report->last())->filename;

        return json_encode($metadata);
    }

    /**
     * Init the Repository
     * 
     * @param string $repository_address
     * 
     * @return void
     */
    public function initRepository(string $repository_address)
    {
        $this->repo = GitRepo::create($repository_address);
    }

    /**
     * @param string $config_key
     */
    public function getGitConfig(string $config_key) 
    {
        $command = ' config --get ' . $config_key;
        
        try {
            $result = $this->console->runCommand( Git::getBin() . $command );
        } catch (\Exception $e) {
            $return = $e->getMessage();
        }

        return $return;
    }

    /**
     * @param string $config_key
     * @param string $value
     */
    public function setGitConfig(string $config_key, string $value) 
    {
        $command = ' config ' . $config_key . ' "' . $value . '"';
        
        try {
            $result = $this->console->runCommand( Git::getBin() . $command );
        } catch (\Exception $e) {
            $return = $e->getMessage();
        }

        return $return;
    }
}