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

    /**
     * Массовое создание сущностей.
     * Поддерживает вставку сразу в несколько таблиц.
     *
     * @param object[] $entities
     * @param array $replaceTables если какие-то таблицы надо поменять, то надо указать в этот массив замену в виде [origName => realName]
     *
     * @return int
     */
    public function createMulti(array $entities, array $replaceTables = []): int;

    /**
     * Массовое обновление сущностей.
     * Поддерживает обновление сразу нескольких таблиц.
     *
     * @param object[] $entities
     *
     * @return int
     */
    public function updateMulti(array $entities): int;
}
