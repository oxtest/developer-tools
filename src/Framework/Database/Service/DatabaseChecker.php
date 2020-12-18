<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\DeveloperTools\Framework\Database\Service;

use PDO;

class DatabaseChecker implements DatabaseCheckerInterface
{
    /** @inheritDoc */
    public function doesDatabaseExist(
        string $host,
        int $port,
        string $user,
        string $password,
        string $name
    ): bool {
        $connection = $this->getDatabaseConnection($host, $port, $user, $password);
        try {
            $connection->exec("USE `{$name}`");
        } catch (\PDOException $e) {
            return false;
        }
        return true;
    }

    /**
     * @param string $host
     * @param int    $port
     * @param string $user
     * @param string $password
     *
     * @return PDO
     */
    private function getDatabaseConnection(string $host, int $port, string $user, string $password): PDO
    {
        return new \PDO(
            sprintf('mysql:host=%s;port=%s', $host, $port),
            $user,
            $password,
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
        );
    }
}
