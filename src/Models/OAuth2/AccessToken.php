<?php

namespace Models\OAuth2;

use Models\Abstraction\GitDAO;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;

use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;

use Models\Traits\GitWorkflow;

use Models\Interfaces\FileSystemInterface;
use Models\Interfaces\GitInterface;
use Models\Interfaces\BagInterface;

class AccessToken extends GitDAO implements AccessTokenEntityInterface
{
    use AccessTokenTrait, EntityTrait, TokenEntityTrait, GitWorkflow;

    protected $repo;

    protected $database = 'oauth/access_token';

    public function __construct(
        FileSystemInterface $filesystem,
        GitInterface $git,
        BagInterface $bag,
        array $config = []
    )
    {
        parent::__construct($filesystem, $git, $bag);

        // This is necessary to accomplish with specific
        // models what is being done on the generic.
        if (isset($this->git)) {
            $this->git->setRepo($this->config['database-address'] . DIRECTORY_SEPARATOR . $this->database);
        }

        $this->setJsonStructure(true);
    }

    /**
     * We will never keep cache for this db.
     */
    protected function resolveCacheCondition()
    {
        // --
    }

}
