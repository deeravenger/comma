<?php
declare(strict_types=1);

namespace Comma;

use Comma\Exception\ContainerInvalidParameter;
use Comma\Exception\ContainerInvalidReference;

class Container implements ContainerInterface
{
    /**
     * @var array
     */
    protected $container = [];
    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * Container constructor.
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->parameters = $parameters;
    }

    /**
     * @param string $id
     * @param object $service
     * @return ContainerInterface
     */
    public function set(string $id, $service): ContainerInterface
    {
        $this->container[$id] = $service;

        return $this;
    }

    /**
     * @param string $id
     * @param int $invalidBehavior
     * @return mixed
     * @throws \Comma\Exception\ContainerInvalidReference
     */
    public function get(string $id, int $invalidBehavior = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE)
    {
        $result = null;
        if (ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE === $invalidBehavior) {
            if (!$this->has($id)) {
                throw new ContainerInvalidReference(sprintf('Invalid reference %s', $id), 500);
            } else {
                $result = $this->container[$id];
            }
        } elseif (ContainerInterface::IGNORE_ON_INVALID_REFERENCE === $invalidBehavior) {
            if ($this->has($id)) {
                $result = $this->container[$id];
            }
        }
        return $result;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->container[$id]);
    }

    /**
     * @param string $name
     * @param $value
     * @return ContainerInterface
     */
    public function setParameter(string $name, $value): ContainerInterface
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    /**
     * @param string $name
     * @return mixed
     * @throws \Comma\Exception\ContainerInvalidParameter
     */
    public function getParameter(string $name)
    {
        if (!$this->hasParameter($name)) {
            throw new ContainerInvalidParameter(sprintf('Parameter %s does not exists', $name), 500);
        }
        return $this->parameters[$name];
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasParameter(string $name): bool
    {
        return isset($this->parameters[$name]);
    }
}