<?php

namespace CyberApp\UploaderBundle\EventListener\Doctrine;

use Doctrine\Common\EventSubscriber;

use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class BaseListener implements EventSubscriber
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Listener constructor
     *
     * @param ContainerInterface $container DI container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
}
