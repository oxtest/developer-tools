<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\DeveloperTools\Tests\Integration\Framework\Database\Service;

use OxidEsales\DeveloperTools\Framework\Database\Service\DatabaseChecker;
use OxidEsales\Facts\Config\ConfigFile;
use PHPUnit\Framework\TestCase;

class DatabaseCheckerTest extends TestCase
{
    public function testDoesDatabaseExistWithExistingDatabase(): void
    {
        $configFile = new ConfigFile();
        $existingDatabaseName = $configFile->getVar('dbName');

        $this->assertTrue((new DatabaseChecker())
            ->doesDatabaseExist(
                $configFile->getVar('dbHost'),
                (int) $configFile->getVar('dbPort'),
                $configFile->getVar('dbUser'),
                $configFile->getVar('dbPwd'),
                $existingDatabaseName
            )
        );
    }

    public function testDoesDatabaseExistWithNewDatabaseName(): void
    {
        $configFile = new ConfigFile();
        $nonExistingDatabaseName = uniqid('some-string-', true);

        $this->assertFalse((new DatabaseChecker())
            ->doesDatabaseExist(
                $configFile->getVar('dbHost'),
                (int) $configFile->getVar('dbPort'),
                $configFile->getVar('dbUser'),
                $configFile->getVar('dbPwd'),
                $nonExistingDatabaseName
            )
        );
    }
}
