<?php


namespace Repositories\Abstraction;

use Psr\Container\ContainerInterface;

class AbstractRepository
{
    /** @var ContainerInterface */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
}
