<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\DeveloperTools\Tests\Integration\Framework\Database\Service;

use OxidEsales\DeveloperTools\Framework\Database\Service\DatabaseChecker;
use OxidEsales\DeveloperTools\Framework\Database\Service\DatabaseCreator;
use OxidEsales\DeveloperTools\Framework\Database\Service\DatabaseInitiatorInterface;
use OxidEsales\DeveloperTools\Framework\Database\Service\DatabaseViewsGeneratorInterface;
use OxidEsales\DeveloperTools\Framework\Database\Service\DropDatabaseService;
use OxidEsales\DeveloperTools\Framework\Database\Service\ResetDatabaseService;
use OxidEsales\DeveloperTools\Framework\Database\Service\ResetDatabaseServiceInterface;
use OxidEsales\Facts\Config\ConfigFile;
use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;

final class ResetDatabaseServiceTest extends TestCase
{
    /**
     * @var array
     */
    private $params = [];

    /**
     * @var ResetDatabaseServiceInterface
     */
    private $databaseResetService;

    private $connection;

    public function setUp(): void
    {
        $this->params = $this->getDatabaseConnectionInfo();
        $this->params['dbName'] = 'oxid_reset_db_test';
        $this->connection = $this->getDatabaseConnection();
        $this->databaseResetService = new ResetDatabaseService(
            new DatabaseChecker(),
            new DatabaseCreator(),
            $this->getDatabaseInitiatorMock(),
            new DropDatabaseService(),
            $this->getDatabaseViewGeneratorMock()
        );

        parent::setUp();
    }

    public function tearDown(): void
    {
        $this->dropDatabase($this->params['dbName']);

        parent::tearDown();
    }

    public function testResetNewDatabase(): void
    {
        $this->assertFalse($this->doesDatabaseExist($this->params['dbName']));
        $this->databaseResetService->resetDatabase(
            $this->params['dbHost'],
            $this->params['dbPort'],
            $this->params['dbUser'],
            $this->params['dbPwd'],
            $this->params['dbName']
        );
        $this->assertTrue($this->doesDatabaseExist($this->params['dbName']));
    }

    public function testResetExistingDatabase(): void
    {
        $this->databaseResetService->resetDatabase(
            $this->params['dbHost'],
            $this->params['dbPort'],
            $this->params['dbUser'],
            $this->params['dbPwd'],
            $this->params['dbName']
        );
        $this->databaseResetService->resetDatabase(
            $this->params['dbHost'],
            $this->params['dbPort'],
            $this->params['dbUser'],
            $this->params['dbPwd'],
            $this->params['dbName']
        );
        $this->assertTrue($this->doesDatabaseExist($this->params['dbName']));
    }

    public function testCreateDatabaseWhenDatabaseCredentialsIsIncorrect(): void
    {
        $this->expectException(PDOException::class);
        $this->databaseResetService->resetDatabase(
            $this->params['dbHost'],
            $this->params['dbPort'],
            '',
            '',
            $this->params['dbName']
        );
    }

    private function getDatabaseConnectionInfo(): array
    {
        $configFile = new ConfigFile();

        return [
            'dbHost' => $configFile->getVar('dbHost'),
            'dbPort' => (int) $configFile->getVar('dbPort'),
            'dbUser' => $configFile->getVar('dbUser'),
            'dbPwd'  => $configFile->getVar('dbPwd')
        ];
    }

    /**
     * @throws \Exception
     */
    private function dropDatabase($name): void
    {
        try {
            if ($this->doesDatabaseExist($name)) {
                $this->connection->exec("DROP DATABASE `{$name}`");
            }
        } catch (\Throwable $exception) {
            throw new \Exception('Failed: Could not drop database');
        }
    }
    
    public function doesDatabaseExist($name): bool 
    {
        try {
            $this->connection->exec("USE `{$name}`");
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
    private function getDatabaseConnection(): PDO
    {
        return new \PDO(
            sprintf('mysql:host=%s;port=%s', $this->params['dbHost'], $this->params['dbPort']),
            $this->params['dbUser'],
            $this->params['dbPwd'],
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
        );
    }

    private function getDatabaseViewGeneratorMock(): DatabaseViewsGeneratorInterface
    {
        return $this->prophesize(DatabaseViewsGeneratorInterface::class)->reveal();
    }

    private function getDatabaseInitiatorMock(): DatabaseInitiatorInterface
    {
        return $this->prophesize(DatabaseInitiatorInterface::class)->reveal();
    }
}

