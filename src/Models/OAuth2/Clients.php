<?php

namespace Models\OAuth2;

use Exception;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use League\OAuth2\Server\Entities\ClientEntityInterface;

use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\ClientTrait;

use Models\Interfaces\FileSystemInterface;
use Models\Interfaces\GitInterface;
use Models\Interfaces\BagInterface;
use Models\GitDAO;
use Models\Abstraction\GitDAO as AbstractGitDAO;
use Models\Traits\GitWorkflow;

/**
 * Format of data:
 * {
 *     "name": string,
 *     "user_id": integer,
 *     "redirect_uri": string
 * }
 */
class Clients extends AbstractGitDAO implements ClientEntityInterface
{

    use EntityTrait;
    use ClientTrait;

    use GitWorkflow;

    protected $database = "oauth/clients";

    protected $repo;

    public function __construct(
        FileSystemInterface $filesystem,
        GitInterface $git,
        BagInterface $bag,
        array $config = []
    )
    {
        parent::__construct($filesystem, $git, $bag);

        // this is necessary to acomplish with specific
        // models what is being done on the generic
        if (isset($this->git)) {
            $this->git->setRepo($this->config['database-address'] . '/' . $this->database);
        }
    }

    /**
     * @param int|string $id
     *
     * @return $this|array
     * @throws Exception
     */
    public function find($id)
    {
        $client_loaded = parent::find($id . '.json');

        $result_parsed = json_decode($client_loaded, true);

        $this->file_content = $this->filesystem->loadFileObject($result_parsed);

        $this->setIdentifier($id);

        return $this;
    }

}
