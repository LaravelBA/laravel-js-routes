<?php
namespace LaravelBA\LaravelJsRoutes\Generators;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;

class RoutesJavascriptGenerator
{
    /**
     * File system instance
     *
     * @var Filesystem
     */
    protected $file;

    /**
     * Router instance
     *
     * @var Router
     */
    protected $router;

    public function __construct(Filesystem $file, Router $router)
    {
        $this->file   = $file;
        $this->router = $router;
    }

    /**
     * Compile routes template and generate
     *
     * @param  string $path
     * @param  string $name
     * @param  array  $options
     *
     * @return bool
     */
    public function make($path, $name, array $options = [])
    {
        $options += ['middleware' => null, 'prefix' => null];

        $parsedRoutes = $this->getParsedRoutes($options['middleware'], $options['prefix']);

        $template = $this->file->get(__DIR__ . '/templates/Router.js');

        $template = str_replace("routes: null,", 'routes: ' . json_encode($parsedRoutes) . ',', $template);
        $template = str_replace("'Router'", "'" . $options['object'] . "'", $template);

        if ($this->file->isWritable($path)) {
            $filename = $path . '/' . $name;

            return $this->file->put($filename, $template) !== false;
        }

        return false;
    }

    /**
     * Get parsed routes
     *
     * @param  string $middleware
     * @param  string $prefix
     *
     * @return array
     */
    protected function getParsedRoutes($middleware = null, $prefix = null)
    {
        /** @var Collection $parsedRoutes */
        $parsedRoutes = Collection::make($this->router->getRoutes()->getRoutes())
            ->map(function ($route) {
                return $this->getRouteInformation($route);
            })
            ->filter();

        if ($middleware) {
            $parsedRoutes = $parsedRoutes->filter(function($routeInfo) use ($middleware) {
                return in_array($middleware, $routeInfo['middleware']);
            });
        }

        $parsedRoutes = $parsedRoutes->map(function($routeInfo){
            unset($routeInfo['middleware']);

            return $routeInfo;
        });

        if ($prefix) {
            $parsedRoutes = $parsedRoutes->map(function ($routeInfo) use ($prefix) {
                $routeInfo['uri'] = $prefix . $routeInfo['uri'];

                return $routeInfo;
            });
        }

        return $parsedRoutes->values()->all();
    }

    /**
     * Get the route information for a given route.
     *
     * @param  \Illuminate\Routing\Route $route
     *
     * @return array|null
     */
    protected function getRouteInformation(Route $route)
    {
        if ($route->getName()) {
            return [
                'uri'        => $route->uri(),
                'name'       => $route->getName(),
                'middleware' => $route->middleware(),
            ];
        }

        return null;
    }
}
