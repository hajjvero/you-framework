<?php

declare(strict_types=1);

namespace YouRoute\Router;

use YouRoute\Attribute\Route;

/**
 * Collection de routes.
 *
 * Cette classe permet de stocker et gérer l'ensemble des routes enregistrées
 * dans l'application.
 */
class RouteCollection
{
    /**
     * @var array<string, Route[]> Tableau associatif stockant les routes par méthode HTTP.
     * Structure: ['GET' => [Route1, Route2], 'POST' => [...]]
     */
    private array $routes = [];

    /**
     * Ajoute une route à la collection.
     *
     * @param Route $route L'instance de l'attribut Route à ajouter.
     * @return void
     */
    public function add(Route $route): void
    {
        // Enregistre la route pour chacune des méthodes HTTP qu'elle supporte
        foreach ($route->getMethods() as $method) {
            $this->routes[$method][] = $route;
        }
    }

    /**
     * Récupère toutes les routes enregistrées.
     *
     * @return array<string, Route[]> Tableau des routes groupées par méthode HTTP.
     */
    public function all(): array
    {
        return $this->routes;
    }

    /**
     * Récupère une route par son nom.
     *
     * @param string $name Le nom de la route à rechercher.
     * @return Route|null L'instance de la route si trouvée, sinon null.
     */
    public function getRouteByName(string $name): ?Route
    {
        foreach ($this->routes as $methodRoutes) {
            foreach ($methodRoutes as $route) {
                if ($route->getName() === $name) {
                    return $route;
                }
            }
        }

        return null;
    }


    /**
     * Applique le préfixe d'une classe (Route) sur une méthode (Route).
     *
     * Si une classe de contrôleur possède un attribut Route avec un chemin (ex: "/api"),
     * ce chemin est préfixé au chemin de la méthode.
     *
     * @param Route|null $routeClass L'attribut Route de la classe (peut être null).
     * @param Route $routeMethod L'attribut Route de la méthode.
     * @return Route La route de la méthode mise à jour avec le chemin complet.
     */
    public function prefix(?Route $routeClass, Route $routeMethod): Route
    {
        // Si la classe a une route définie, on concatène son chemin
        if ($routeClass) {
            // Concaténation propre : Route::setPath gère déjà la normalisation,
            // mais on assure ici de ne pas doubler les slashs par sécurité lors de la concaténation brute.
            $prefix = rtrim($routeClass->getPath(), '/');
            $suffix = $routeMethod->getPath();

            // Assure que le suffixe commence par / si le préfixe n'est pas vide
            if ($prefix !== '' && !str_starts_with($suffix, '/')) {
                $suffix = '/' . $suffix;
            }

            $routeMethod->setPath($prefix . $suffix);
        }

        return $routeMethod;
    }
}