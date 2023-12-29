<?php

namespace trinity\router;

use ReflectionException;
use trinity\contracts\{http\RequestInterface,
    http\UriInterface,
    router\RouterInterface,
    router\RoutesCollectionInterface};
use trinity\DIContainer;
use trinity\exception\baseException\LogicException;

final class Router implements RouterInterface
{
    /**
     * @param RequestInterface $request
     * @param UriInterface $uri
     * @param RoutesCollectionInterface $routesCollection
     * @param DIContainer $container
     */
    public function __construct(
        private RequestInterface          $request,
        private UriInterface              $uri,
        private RoutesCollectionInterface $routesCollection,
        private DIContainer               $container,
    )
    {
    }

    /**
     * @return object
     * @throws LogicException
     * @throws ReflectionException
     */
    public function dispatch(): object
    {
        $this->runGlobalMiddlewareAction($this->routesCollection);

        $matchedRoute = $this->findMatchedRoutes();

        $this->runMiddlewareAction($matchedRoute);

        $controllersAction = $this->verificationAction($matchedRoute->getControllerAction());

        if (empty($this->uri->getQueryArg()) === false) {
            return $this->container->call($controllersAction['controllerName'], $controllersAction['actionName'], $this->uri->getQueryArg());
        }

        return $this->container->call($controllersAction['controllerName'], $controllersAction['actionName']);
    }

    /**
     * @return Route|false
     */
    private function findMatchedRoutes(): Route|false
    {
        $matchedRoute = null;

        /** @var Route $item */
        foreach ($this->routesCollection->getRoutes() as $route) {
            foreach ($route as $item) {
                if ($this->request->getMethod() === $item->getMethod() && (bool)preg_match($item->getUrl()['quoteUrl'], $this->uri->getRoute()) === true) {
                    preg_match($item->getUrl()['quoteUrl'], $this->uri->getRoute(), $q);
                    array_shift($q);

                    $requiredParams = $item->getUrl()['requiredParams'] === [] ? array_combine($item->getUrl()['requiredParams'], $q) : [];
                    $optionalParams = $item->getUrl()['optionalParams'] === [] ? array_combine($item->getUrl()['requiredParams'], $q) : [];

                    $this->request->setRequestParams(array_merge($requiredParams, $optionalParams));

                    $matchedRoute = $item;
                }

                $errorRoute = $item;
            }
        }

        return $matchedRoute ?? $errorRoute;
    }


    /**
     * @param string|callable $controllersAction
     * @return array
     * @throws LogicException
     * @throws ReflectionException
     */
    private function verificationAction(string|callable $controllersAction): array
    {
        $controllersAction = array_combine(
            ['controllerName', 'actionName'],
            explode('::', is_callable($controllersAction) ? $controllersAction() : $controllersAction)
        );

        if (method_exists($controllersAction['controllerName'], $controllersAction['actionName']) === false) {
            throw new LogicException('Метод ' . $controllersAction['controllerName'] . '::' . $controllersAction['actionName'] . ' не найден');
        }

        return $controllersAction;
    }

    /**
     * @param Route $route
     * @return void
     * @throws ReflectionException
     */
    private function runMiddlewareAction(Route $route): void
    {
        $this->processMiddleware($route->getMiddleware());
    }

    /**
     * @param array $middlewares
     * @return void
     * @throws ReflectionException
     */
    private function processMiddleware(array $middlewares): void
    {
        if (empty($middlewares) === true) {
            return;
        }

        foreach ($middlewares as $middleware) {
            $instance = $this->container->get($middleware);
            call_user_func([$instance, 'handle']);
        }
    }

    /**
     * @param RoutesCollectionInterface $routesCollection
     * @return void
     * @throws ReflectionException
     */
    private function runGlobalMiddlewareAction(RoutesCollectionInterface $routesCollection): void
    {
        $this->processMiddleware($routesCollection->getGlobalMiddlewares());
    }

    /**
     * @return string
     */
    public function getTypeResponse(): string
    {
        return $this->findMatchedRoutes()->getTypeResponse();
    }
}
