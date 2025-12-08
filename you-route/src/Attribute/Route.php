<?php

namespace YouRoute\Attribute;

#[\Attribute]
final class Route
{
    /**
     * @var string $path le chemin de la route
     */
    private string $path;

    /**
     * @var string $name le nom de la route
     */
    private string $name;

    /**
     * @var string|array $methods les méthodes de la route
     */
    private string|array $methods;

    /**
     * @var array $action l'action de la route (le controller avec la méthode)
     */
    private array $action;

    /**
     * @param string $name le nom de la route
     * @param string $path le chemin de la route
     * @param string|array $methods les méthodes de la route
     */
    public function __construct(string $path, string $name = "", string|array $methods = "GET")
    {
        // Normaliser le chemin
        $this->path = rtrim($path, '/');
        if ($this->path === '') {
            $this->path = '/';
        }

        $this->name = $name;

        // Transformer en tableau si string
        $methods = is_string($methods) ? [$methods] : $methods;

        // Valider les méthodes HTTP
        $validHttpMethods = ["GET", "POST", "PUT", "DELETE", "PATCH", "OPTIONS"];

        foreach ($methods as $method) {
            if (!in_array(strtoupper($method), $validHttpMethods, true)) {
                throw new \InvalidArgumentException("Invalid HTTP method: {$method}");
            }
        }

        $this->methods = array_map('strtoupper', array_unique($methods));
    }


    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = !str_ends_with($path,"/") ? $path."/" : $path;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return array|string
     */
    public function getMethods(): array|string
    {
        return $this->methods;
    }

    /**
     * @return array
     */
    public function getAction(): array
    {
        return $this->action;
    }

    /**
     * @param array $action
     */
    public function setAction(array $action): void
    {
        $this->action = $action;
    }
}