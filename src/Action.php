<?php

declare(strict_types=1);

namespace Compass;

use Error;
use ReflectionException;
use ReflectionFunction;
use Compass\Exception\InvalidActionException;

readonly class Action
{
    /**
     * @param Route $route
     * @param array<string, string> $params
     * @param array<string, mixed> $queryParams
     * @param array<string, mixed> $parsedBody
     */
    public function __construct(private Route $route, private array $params, private array $queryParams, private array $parsedBody)
    {
    }

    /**
     * @param mixed ...$args
     * @return mixed
     * @throws InvalidActionException
     */
    public function call(mixed ...$args): mixed
    {
        try {
            $action = require $this->route->getAction();
            $reflection = new ReflectionFunction($action);
            $actionArgs = [];
            foreach ($reflection->getParameters() as $parameter) {
                if (isset($args[$parameter->getName()])) {
                    $actionArgs[$parameter->getName()] = $args[$parameter->getName()];
                }
                if ($parameter->getName() === 'route') {
                    $actionArgs['route'] = $this->route;
                }
                if ($parameter->getName() === 'params') {
                    $actionArgs['params'] = $this->params;
                }
                if ($parameter->getName() === 'queryParams') {
                    $actionArgs['queryParams'] = $this->queryParams;
                }
                if ($parameter->getName() === 'parsedBody') {
                    $actionArgs['parsedBody'] = $this->parsedBody;
                }
            }
            return call_user_func_array($action, $actionArgs);
        } catch (Error|ReflectionException $error) {
            throw new InvalidActionException(sprintf('%s in action %s', $error->getMessage(), $this->route->getAction()), 0, $error);
        }
    }
}