<?php
/**
 * User: tuttarealstep
 * Date: 26/04/17
 * Time: 19.39
 */

namespace Honey\Container;

use DI\Container as DiContainer;
use DI\ContainerBuilder;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class Container implements ContainerInterface
{
    /**
     * @var \DI\Container
     */
    protected $container;

    /**
     * Container constructor.
     * @param DiContainer $container
     * @param ContainerBuilder $containerBuilder
     */
    function __construct($container = null, $containerBuilder = null)
    {
        if($container == null)
        {
            if($containerBuilder != null && $containerBuilder instanceof ContainerBuilder)
            {
                $this->container = $containerBuilder->build();
            } else {
                $container = new ContainerBuilder();
                $this->container = $container->build();
            }

        } elseif ($container != null && $container instanceof DiContainer)
        {
            $this->container = $container;
        }
    }

    /**
     * @param \DI\Container $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }

    /**
     * @return \DI\Container
     */
    public function getContainer(): DiContainer
    {
        return $this->container;
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get($id)
    {
        return $this->container->get($id);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has($id)
    {
        return $this->container->has($id);
    }

    /**
     * @param $id
     * @param $value
     */
    public function set($id, $value)
    {
        $this->container->set($id, $value);
    }


    /**
     * @param $name
     * @return bool
     */
    public function __get($name)
    {
        if($this->has($name))
        {
            return $this->get($name);
        }

        return false;
    }
}