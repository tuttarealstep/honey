<?php
/**
 * User: tuttarealstep
 * Date: 06/05/17
 * Time: 12.38
 */

namespace Honey\Router;

use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;

class CachedRouter extends Router
{
    function setUpRouterCollector(callable $routeDefinitionCallback = null, array $options = [])
    {
        $options += ['cacheDisabled' => false];

        if (!isset($options['cacheFile'])) {
            throw new \LogicException('Must specify "cacheFile" option');
        }

        if (!$options['cacheDisabled'] && file_exists($options['cacheFile'])) {
            $dispatchData = require $options['cacheFile'];
            if (!is_array($dispatchData)) {
                throw new \RuntimeException('Invalid cache file "' . $options['cacheFile'] . '"');
            }
            return new $options['dispatcher']($dispatchData);
        }

        $this->routeCollector = new RouteCollector(new Std(), new GroupCountBased());

        if($routeDefinitionCallback != null && is_callable($routeDefinitionCallback))
        {
            $routeDefinitionCallback($this->routeCollector);
        }

        /** @var RouteCollector $routeCollector */
        $dispatchData = $this->routeCollector->getData();
        if (!$options['cacheDisabled'])
        {
            file_put_contents(
                $options['cacheFile'],
                '<?php return ' . var_export($dispatchData, true) . ';'
            );
        }
    }
}