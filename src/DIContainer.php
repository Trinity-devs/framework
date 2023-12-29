<?php

namespace trinity;

use trinity\contracts\container\ContainerInterface;
use trinity\exception\baseException\LogicException;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class DIContainer implements ContainerInterface
{
    private static ?self $instanceContainer = null;
    private array $dependentsList;
    private array $singletons = [];

    /**
     * @param array $config
     */
    private function __construct(array $config)
    {
        $this->dependentsList = $config;
    }

    /**
     * @param array $config
     * @return self
     * @throws LogicException
     */
    public static function createContainer(array $config): self
    {
        if (self::$instanceContainer !== null) {
            throw new LogicException('Контейнер не может быть создан дважды.');
        }

        self::$instanceContainer = new self($config);

        return self::$instanceContainer;
    }

    /**
     * @param string $className
     * @return mixed
     * @throws ReflectionException
     */
    public function build(string $className): object
    {
        if (method_exists($className, '__construct') === false) {
            return new $className();
        }

        $reflectionConstruct = new ReflectionMethod($className, '__construct');
        $dependencies = [];

        foreach ($reflectionConstruct->getParameters() as $parameter) {
            $dependencyType = $parameter->getType()->getName();

            if (in_array($dependencyType, ['int', 'string'])) {
                continue;
            }

            $dependencies[] = $this->resolveDependency($dependencyType);
        }

        return new $className(...$dependencies);
    }

    /**
     * @param string $dependenceType
     * @return mixed
     * @throws ReflectionException
     */
    private function resolveDependency(string $dependenceType): mixed
    {
        if (isset($this->dependentsList[$dependenceType])) {
            return $this->singleton($dependenceType);
        }

        if (isset($this->container[$dependenceType]) === false) {
            return $this->build($dependenceType);
        }

        return $this->singletons[$dependenceType];
    }

    /**
     * @param string $className
     * @param string $methodName
     * @param array $args
     * @return mixed
     * @throws LogicException
     * @throws ReflectionException
     */
    public function call(string $className, string $methodName, array $args = []): mixed
    {
        $reflectionMethod = new ReflectionMethod("$className::$methodName");

        $parameters = $reflectionMethod->getParameters();

        $dependencies = [];

        foreach ($parameters as $parameter) {
            if ($parameter->getType() === null) {
                throw new LogicException('Требуется указать тип параметра $' . $parameter->getName());
            }

            if (interface_exists($parameter->getType()->getName()) === false && class_exists($parameter->getType()->getName()) === false) {
                continue;
            }

            $dependency = $parameter->getType()->getName();
            $dependencies[] = $this->get($dependency);
        }

        return $reflectionMethod->invokeArgs($this->build($className), array_merge($dependencies, $args));
    }

    /**
     * @param $interfaceId
     * @return object
     * @throws ReflectionException
     */
    public function singleton($interfaceId): object
    {
        if (isset($this->singletons[$interfaceId]) === false) {
            $this->singletons[$interfaceId] = $this->buildDependency($interfaceId);
        }

        return $this->singletons[$interfaceId];
    }

    /**
     * @param string $dependencyName
     * @return object
     * @throws ReflectionException
     */
    private function buildDependency(string $dependencyName): object
    {
        $className = $this->dependentsList[$dependencyName] ?? $dependencyName;

        if (is_callable($className) === true) {
            return $this->dependentsList[$dependencyName]($this);
        }

        $reflectionClass = new ReflectionClass($className);

        if ($reflectionClass->isInstantiable() === false || $reflectionClass->isCloneable() === false){
            throw new ReflectionException('Экземпляр класса ' . $className . ' не может быть создан');
        }

        $constructor = $reflectionClass->getConstructor();

        if ($constructor === null) {
            return new $className();
        }

        if (is_object($className) === true) {
            return $className;
        }

        $dependencies = [];

        foreach ($constructor->getParameters() as $parameter) {
            if (interface_exists($parameter->getType()->getName()) === false && class_exists($parameter->getType()->getName()) === false) {
                continue;
            }

            $dependencyInterface = $parameter->getType()->getName();
            $dependencies[] = $this->singleton($dependencyInterface);
        }

        return $reflectionClass->newInstanceArgs($dependencies);
    }

    /**
     * @param $interfaceId
     * @return object
     * @throws ReflectionException
     */
    public function get($interfaceId): object
    {
        return $this->buildDependency($interfaceId);
    }

    /**
     * @inheritDoc
     */
    public function has($interfaceId): bool
    {
        return isset($this->dependentsList[$interfaceId]);
    }

    /**
     * @return mixed
     * @throws LogicException
     */
    private function __clone()
    {
        throw new LogicException('Контейнер не может быть клонирован.');
    }
}
