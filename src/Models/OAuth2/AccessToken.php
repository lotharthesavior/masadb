<?php

namespace Models\OAuth2;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
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
        BagInterface $bag
    ){
        parent::__construct($filesystem, $git, $bag);

        // this is necessary to acomplish with specific 
        // models what is being done on the generic
        if( isset($this->git) )
            $this->git->setRepo( $this->config['database-address'] . '/' . $this->database );
    }

    /**
     * We will never keep cache for this db
     */
    protected function resolveCacheCondition()
    {
        // --
    }

}