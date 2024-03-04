<?php

namespace trinity\contracts\container;

interface ContainerInterface
{
    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $interfaceId Identifier of the entry to look for.
     *
     * @return mixed Entry.
     *@throws ContainerExceptionInterface Error while retrieving the entry.
     *
     */
    public function get($interfaceId);

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $interfaceId Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has($interfaceId);
}
