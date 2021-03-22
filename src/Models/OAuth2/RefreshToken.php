<?php

namespace Models\OAuth2;

use Models\Interfaces\BagInterface;
use Models\Interfaces\FileSystemInterface;
use Models\Interfaces\GitInterface;
use Models\Traits\GitWorkflow;

use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;

use League\OAuth2\Server\Entities\Traits\RefreshTokenTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

class RefreshToken implements RefreshTokenEntityInterface
{
    use EntityTrait, RefreshTokenTrait, GitWorkflow;

    protected $repo;

    protected $database = 'oauth/refresh_token';

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
