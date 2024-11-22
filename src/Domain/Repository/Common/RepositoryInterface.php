<?php

namespace App\Domain\Repository\Common;

interface RepositoryInterface
{
    /**
     * @template T of object
     * @param T&object $entity
     *
     * @return bool
     */
    public function create(object $entity): bool;

    /**
     * @template T of object
     * @param T&object $entity
     *
     * @return bool
     */
    public function update(object $entity): bool;

    /**
     * @template T of object
     * @param T&object $entity
     *
     * @return bool
     */
    public function delete(object $entity): bool;
}
