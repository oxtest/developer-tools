<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\DeveloperTools\Framework\Database\Service;

interface DatabaseCheckerInterface
{
    /**
     * @param string $host
     * @param int $port
     * @param string $user
     * @param string $password
     * @param string $name
     * @return bool
     */
    public function doesDatabaseExist(
        string $host,
        int $port,
        string $user,
        string $password,
        string $name
    ): bool;
}
