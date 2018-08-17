<?php
declare(strict_types=1);

namespace Comma;

use Comma\Exception\ViewInvalidPath;

/**
 * Application class
 * @package Comma
 */
class Application
{
    const SERVICE_NAME_REQUEST = 'request';
    const SERVICE_NAME_ROUTER = 'router';
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Application constructor.
     * @param Container $container
     * @throws Exception
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        if (!$this->container->has(static::SERVICE_NAME_REQUEST)) {
            $this->container->set(static::SERVICE_NAME_REQUEST, new Request());
        }
        if (!$this->container->has(static::SERVICE_NAME_ROUTER)) {
            $this->container->set(static::SERVICE_NAME_ROUTER, new Router());
        }
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->container->get(static::SERVICE_NAME_REQUEST);
    }

    /**
     * @param string $path
     * @param array $data
     * @return View
     * @throws \Comma\Exception\ViewInvalidPath
     */
    public function view(string $path, array $data = []): View
    {
        $path = rtrim($this->container->getParameter('view.path'), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.ltrim(
                $path,
                DIRECTORY_SEPARATOR
            );
        if (!is_file($path)) {
            throw new ViewInvalidPath(sprintf('Invalid template path "%s". Check parameter "view.path"', $path));
        }

        return new View($path, $data);
    }

    /**
     * @param string $pattern
     * @param string|callable $controller
     * @return Route
     * @throws \Comma\Exception
     */
    public function route(string $pattern, $controller): Route
    {
        $route = new Route($pattern, $controller);
        $this->getRouter()->append($route);

        return $route;
    }

    /**
     * @return Router
     */
    public function getRouter(): Router
    {
        return $this->container->get(static::SERVICE_NAME_ROUTER);
    }

    /**
     * Run application
     * @param string $requestedUri
     * @throws \Comma\Exception
     */
    public function run($requestedUri)
    {
        $route = $this->getRouter()->match($requestedUri);
        $response = $route->handle();
        if ($response instanceof ResponseInterface) {
            $content = $response
//                ->sendHeaders()
                ->getContent();
        } else {
            $content = (string)$response;
        }

        file_put_contents('php://stdout', $content);
    }
}
