<?php

namespace YouRoute\Router;

use YouRoute\Attribute\Route;
use YouRoute\Http\Response;
use ReflectionClass;
use ReflectionException;
use YouRoute\Router\Abstract\AbstractRouteResolver;

class RouteResolver extends AbstractRouteResolver
{
    public function __construct(private readonly RouteCollection $routeCollection)
    {}

    /**
     * @throws ReflectionException
     */
    public function loadRoutesFromDirectory(string $resourceDir): void
    {
        $controllers = $this->loadAllClassNames($resourceDir);

        /**
         * @var ReflectionClass[] $reflection
         */
        $reflections = array_map(static fn($controller) => new ReflectionClass($controller), $controllers);
        $this->prepareRoute($reflections);
    }

    /**
     * @param ReflectionClass[] $reflections
     * @return void
     * @throws ReflectionException
     */
    private function prepareRoute(array $reflections): void
    {
        foreach ($reflections as $reflection) {
            // check si class est abstract
            if (!$reflection->isInstantiable()) {
                throw new ReflectionException("The abstract class {$reflection->getName()} not access in system route");
            }

            // Créer instance attribute route du class s'il existe
            $routeController = array_find($reflection->getAttributes(), static fn($attribute) => $attribute->getName() === Route::class)?->newInstance();

            // prepare route method
            foreach ($reflection->getMethods() as $method) {
                /**
                 * Créer instance attribute route du method s'il existe
                 * @var ?Route $routeMethod
                 */
                $routeMethod = array_find($method->getAttributes(), static fn($attribute) => $attribute->getName() === Route::class)?->newInstance();

                if (!$routeMethod) {
                    continue;
                }

                // Ajouter action dans instance route avec class et method
                $routeMethod->setAction([$method->class, $method->name]);

                // check modifier
                if (!$method->isPublic()) {
                    throw new ReflectionException("The modifier of {$method->class}::{$method->getName()} not access in system route");
                }

                // check return type
                if ($method->getReturnType()?->getName() !== Response::class) {
                    throw new ReflectionException("The return type of {$method->class}::{$method->getName()} not response in system route");
                }

                $this->routeCollection->add($this->routeCollection->prefix($routeController, $routeMethod));
            }
        }
    }

}