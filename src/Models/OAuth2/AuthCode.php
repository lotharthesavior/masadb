<?php

namespace Models\OAuth2;

use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use Models\Abstraction\GitDAO;

use League\OAuth2\Server\Entities\AuthCodeEntityInterface;

use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;
use League\OAuth2\Server\Entities\Traits\AuthCodeTrait;
use Models\Interfaces\BagInterface;
use Models\Interfaces\FileSystemInterface;
use Models\Interfaces\GitInterface;
use Models\Traits\GitWorkflow;

class AuthCode extends GitDAO implements AuthCodeEntityInterface
{
    use AuthCodeTrait, EntityTrait, TokenEntityTrait, GitWorkflow;

    protected $repo;

    protected $database = 'oauth/auth_code';

    public function __construct(
        FileSystemInterface $filesystem,
        GitInterface $git,
        BagInterface $bag,
        array $config = []
    ) {
        parent::__construct($filesystem, $git, $bag);

        // This is necessary to accomplish with specific
        // models what is being done on the generic.
        if (isset($this->git)) {
            $this->git->setRepo($this->config['database-address'] . DIRECTORY_SEPARATOR . $this->database);
        }

        $this->setJsonStructure(true);
    }

}
