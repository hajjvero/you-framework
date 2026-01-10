<?php

declare(strict_types=1);

namespace YouKernel\Component\Container;

use Closure;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use RuntimeException;

/**
 * Class Container
 *
 * Un conteneur de services léger avec support de l'auto-wiring.
 *
 * @package YouKernel
 */
class Container
{
    /**
     * @var array<string, mixed> Service instances storage.
     */
    private array $instances = [];

    /**
     * @var array<string, mixed> Definitions (factories or classes) storage.
     */
    private array $definitions = [];

    /**
     * Set a service or value.
     *
     * @param string $id       The service identifier (usually the class name).
     * @param mixed  $concrete The instance, factory closure, or class name.
     */
    public function set(string $id, mixed $concrete): void
    {
        $this->definitions[$id] = $concrete;
    }

    /**
     * Check if a service is defined or instantiated.
     */
    public function has(string $id): bool
    {
        return isset($this->instances[$id]) || isset($this->definitions[$id]) || class_exists($id);
    }

    /**
     * Resolve and return a service.
     */
    public function get(string $id): mixed
    {
        // 1. If already instantiated, return it.
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        // 2. Si une définition existe
        if (isset($this->definitions[$id])) {
            $concrete = $this->definitions[$id];

            // Si c'est une closure, on l'exécute pour obtenir l'instance
            if ($concrete instanceof Closure) {
                $this->instances[$id] = $concrete($this);
            } else {
                $this->instances[$id] = $concrete;
            }

            return $this->instances[$id];
        }

        // 3. Auto-wiring attempt if it's a valid class
        if (class_exists($id)) {
            $instance = $this->resolve($id);
            $this->instances[$id] = $instance;
            return $instance;
        }

        throw new RuntimeException("Service '$id' not found or cannot be resolved.");
    }

    /**
     * Resolve a class and its dependencies via Reflection.
     */
    private function resolve(string $class): object
    {
        $reflection = new ReflectionClass($class);

        if (!$reflection->isInstantiable()) {
            throw new RuntimeException("Class '$class' is not instantiable.");
        }

        $constructor = $reflection->getConstructor();

        // If no constructor, instantiate directly
        if ($constructor === null) {
            return new $class();
        }

        $dependencies = $this->resolveDependencies($constructor->getParameters(), $class);

        return $reflection->newInstanceArgs($dependencies);
    }

    /**
     * Call a method or closure while automatically injecting its arguments.
     *
     * @param callable $callable
     * @param array $args Manual arguments to override or supplement injection.
     * @return mixed
     */
    public function call(callable $callable, array $args = []): mixed
    {
        if (is_array($callable)) {
            $reflection = new ReflectionMethod($callable[0], $callable[1]);
        } else {
            $reflection = new \ReflectionFunction($callable(...));
        }

        $dependencies = $this->resolveDependencies($reflection->getParameters(), 'callable', $args);

        return call_user_func_array($callable, $dependencies);
    }

    /**
     * Resolve an array of reflection parameters.
     */
    private function resolveDependencies(array $parameters, string $context, array $args = []): array
    {
        $dependencies = [];

        foreach ($parameters as $param) {
            $name = $param->getName();

            // Override with manual arguments if provided
            if (array_key_exists($name, $args)) {
                $dependencies[] = $args[$name];
                continue;
            }

            $type = $param->getType();

            if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
                if ($param->isDefaultValueAvailable()) {
                    $dependencies[] = $param->getDefaultValue();
                    continue;
                }

                if ($param->allowsNull()) {
                    $dependencies[] = null;
                    continue;
                }

                throw new RuntimeException("Cannot resolve parameter '$name' in '$context'.");
            }

            $dependencies[] = $this->get($type->getName());
        }

        return $dependencies;
    }
}
