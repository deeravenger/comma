<?php
declare(strict_types=1);

namespace Comma;

interface ContainerInterface
{
    const IGNORE_ON_INVALID_REFERENCE = 0;
    const EXCEPTION_ON_INVALID_REFERENCE = 1;

    /**
     * @param string $id
     * @param object $service
     * @return ContainerInterface
     */
    public function set(string $id, $service): ContainerInterface;

    /**
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool;

    /**
     * @param string $id
     * @param int $invalidBehavior
     * @return mixed
     */
    public function get(string $id, int $invalidBehavior = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE);

    /**
     * @param string $name
     * @param $value
     * @return ContainerInterface
     */
    public function setParameter(string $name, $value): ContainerInterface;

    /**
     * @param string $name
     * @return mixed
     */
    public function getParameter(string $name);

    /**
     * @param string $name
     * @return bool
     */
    public function hasParameter(string $name): bool;
}
