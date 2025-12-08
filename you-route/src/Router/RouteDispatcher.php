<?php

namespace YouRoute\Router;

use Exception;
use YouRoute\Http\Response;

readonly class RouteDispatcher
{
    public function __construct(private RouteCollection $routeCollection)
    {
    }

    /**
     * @throws Exception
     */
    public function dispatch(string $method, string $url): void
    {
        // get routes by method of request
        $routes = $this->routeCollection->all()[$method] ?? [];

        // add slash to end of url
        $url = !str_ends_with($url, "/") ? $url . "/" : $url;

        foreach ($routes as $route) {
            // match path
            $params = $this->matchPath($route->getPath(), $url);

            // execute action
            if (is_array($params)) {
                $this->dispatchAction($route->getAction(), $params);
                return;
            }
        }

        // not found route
        new Response("<h1 style='text-align: center; margin-top: 20px; font-size: 50px; color: red'>404 Not Found</h1>",404)->send();
    }

    /**
     * Compare un motif d’URL à une URL réelle et extrait les paramètres de route.
     *
     * Cette méthode compare un motif de route (par exemple, '/users/{id}/posts/{postId}')
     * contre une URL réelle (par exemple, '/users/123/posts/456') et extrait les paramètres dynamiques si le motif correspond.
     *
     * @param string $pattern Motif de route
     * @param string $url URL réelle
     * @return bool|array
     */
    private function matchPath(string $pattern, string $url): bool|array
    {
        $patternSegments = explode('/', $pattern);
        $urlSegments = explode('/', $url);

        //  check count segments
        if (count($patternSegments) !== count($urlSegments)) {
            return false;
        }

        // Store parameters
        $params = [];

        // Compare each segment
        foreach ($patternSegments as $index => $patternSegment) {
            // Check if this segment is a parameter (e.g., {id})
            if (preg_match('/^\{(\w+)\}$/', $patternSegment, $match)) {
                // Extract parameter name and value
                $params[$match[1]] = $urlSegments[$index];
            } elseif ($patternSegment !== $urlSegments[$index]) {
                return false;
            }
        }

        return $params;
    }

    /**
     * Execute action
     *
     * @param callable|array $action
     * @param array $params
     * @return void
     */
    private function dispatchAction(callable|array $action, array $params): void
    {
        // check callable
        if (is_callable($action)) {
            call_user_func_array($action, $params); // call function
            return;
        }

        // check array
        if (is_array($action)) {
            [$className, $methodName] = $action; // get class and method
            $instance = new $className(); // create instance
            call_user_func_array([$instance, $methodName], $params); // call method
        }
    }
}