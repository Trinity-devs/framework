<?php

namespace trinity\router;

use trinity\exception\baseException\LogicException;
use trinity\exception\httpException\NotFoundHttpException;
use ReflectionException;
use trinity\contracts\{RequestInterface, RouterInterface, RoutesCollectionInterface, UriInterface};
use trinity\DIContainer;


final class Router implements RouterInterface
{
    /**
     * @param RequestInterface $request
     * @param UriInterface $uri
     * @param RoutesCollectionInterface $routesCollection
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
     * @return mixed
     * @throws LogicException
     * @throws NotFoundHttpException
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
     * @return Route
     * @throws NotFoundHttpException
     */
    private function findMatchedRoutes(): Route
    {
        $matchedRoute = null;

        /** @var Route $item */
        foreach ($this->routesCollection->getRoutes() as $route) {
            foreach ($route as $item) {
                if ($this->request->getMethod() === $item->getMethod() && (bool)preg_match($item->getUrl()['quoteUrl'], $this->uri->getRoute()) === true) {
                    $matchedRoute = $item;
                }
            }
        }

        if ($matchedRoute === null) {
            throw new NotFoundHttpException('Страница не найдена');
        }

        return $matchedRoute;
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
     * @throws LogicException
     * @throws ReflectionException
     */
    private function runMiddlewareAction(Route $route): void
    {
        $this->processMiddleware($route->getMiddleware());
    }

    /**
     * @param array $middlewares
     * @return void
     * @throws \ReflectionException
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
     * @param RoutesCollection $routesCollection
     * @return void
     * @throws LogicException
     * @throws ReflectionException
     */
    private function runGlobalMiddlewareAction(RoutesCollectionInterface $routesCollection): void
    {
        $this->processMiddleware($routesCollection->getGlobalMiddlewares());
    }

    /**
     * @return string
     * @throws NotFoundHttpException
     */
    public function getTypeResponse(): string
    {
        return $this->findMatchedRoutes()->getTypeResponse();
    }
}
