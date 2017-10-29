<?php
/**
 * User: tuttarealstep
 * Date: 06/05/17
 * Time: 11.51
 */

namespace Honey\Router;

use FastRoute\DataGenerator;
use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;
use FastRoute\Dispatcher as DispatcherInterface;
use FastRoute\Dispatcher\GroupCountBased as Dispatcher;

class Router implements DataGenerator, DispatcherInterface
{
    /**
     * @var RouteCollector
     */
    protected $routeCollector;

    function __construct(callable $routeDefinitionCallback = null, array $options = [])
    {
        $this->setUpRouterCollector($routeDefinitionCallback, $options);
    }

    function setUpRouterCollector(callable $routeDefinitionCallback = null, array $options = [])
    {
        $this->routeCollector = new RouteCollector(new Std(), new GroupCountBased());

        if($routeDefinitionCallback != null && is_callable($routeDefinitionCallback))
        {
            $routeDefinitionCallback($this->routeCollector);
        }
    }

    /**
     * @param string|string[] $httpMethod
     * @param string $route
     * @param mixed  $handler
     */
    public function addRoute($httpMethod, $route, $handler)
    {
        $this->routeCollector->addRoute($httpMethod, $route, $handler);
    }

    /**
     * Returns dispatcher data in some unspecified format, which
     * depends on the used method of dispatch.
     */
    public function getData()
    {
        return $this->routeCollector->getData();
    }

    /**
     * Dispatches against the provided HTTP method verb and URI.
     *
     * Returns array with one of the following formats:
     *
     *     [self::NOT_FOUND]
     *     [self::METHOD_NOT_ALLOWED, ['GET', 'OTHER_ALLOWED_METHODS']]
     *     [self::FOUND, $handler, ['varName' => 'value', ...]]
     *
     * @param string $httpMethod
     * @param string $uri
     *
     * @return array
     */
    public function dispatch($httpMethod, $uri)
    {
        $dispatcher = new Dispatcher($this->getData());
        return $dispatcher->dispatch($httpMethod, $uri);
    }

    /**
     * Create a route group with a common prefix.
     *
     * @param string $prefix
     * @param callable $callback
     */
    public function addGroup($prefix, callable $callback)
    {
        $this->routeCollector->addGroup($prefix, $callback);
    }

    /**
     * Adds a GET route to the collection
     *
     * @param string $route
     * @param mixed  $handler
     */
    public function get($route, $handler) {
        $this->addRoute('GET', $route, $handler);
    }

    /**
     * Adds a POST route to the collection
     *
     * @param string $route
     * @param mixed  $handler
     */
    public function post($route, $handler) {
        $this->addRoute('POST', $route, $handler);
    }

    /**
     * Adds a PUT route to the collection
     *
     * @param string $route
     * @param mixed  $handler
     */
    public function put($route, $handler) {
        $this->addRoute('PUT', $route, $handler);
    }

    /**
     * Adds a DELETE route to the collection
     *
     * @param string $route
     * @param mixed  $handler
     */
    public function delete($route, $handler) {
        $this->addRoute('DELETE', $route, $handler);
    }

    /**
     * @param $route
     * @param $handler
     */
    public function patch($route, $handler) {
        $this->addRoute('PATCH', $route, $handler);
    }

    /**
     * @param string $route
     * @param mixed  $handler
     */
    public function head($route, $handler)
    {
        $this->addRoute('HEAD', $route, $handler);
    }

}