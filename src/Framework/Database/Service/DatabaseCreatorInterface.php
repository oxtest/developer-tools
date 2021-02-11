<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\DeveloperTools\Framework\Database\Service;

use OxidEsales\DeveloperTools\Framework\Database\Exception\DatabaseConnectionException;
use OxidEsales\DeveloperTools\Framework\Database\Exception\DatabaseExistsException;

interface DatabaseCreatorInterface
{
    /**
     * @param string $host
     * @param int    $port
     * @param string $username
     * @param string $password
     * @param string $name
     *
     * @throws DatabaseConnectionException
     * @throws DatabaseExistsException
     */
    public function createDatabase(string $host, int $port, string $username, string $password, string $name): void;
}