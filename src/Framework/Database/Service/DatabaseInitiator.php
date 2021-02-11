<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\DeveloperTools\Framework\Database\Service;

use OxidEsales\DoctrineMigrationWrapper\Migrations;
use OxidEsales\DoctrineMigrationWrapper\MigrationsBuilder;
use OxidEsales\DeveloperTools\Framework\Database\Exception\InitiateDatabaseException;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\BasicContextInterface;
use PDO;
use Throwable;
use Webmozart\PathUtil\Path;

/**
 * Class DatabaseInitiator
 *
 * @package OxidEsales\EshopCommunity\Internal\Setup\Database
 */
class DatabaseInitiator implements DatabaseInitiatorInterface
{

    /** @var BasicContextInterface */
    private $context;

    /** @var PDO */
    private $dbConnection;

    /**
     * DatabaseInitiator constructor.
     *
     * @param BasicContextInterface $context
     */
    public function __construct(
        BasicContextInterface $context
    ) {
        $this->context = $context;
    }

    /**
     * @param string $host
     * @param int $port
     * @param string $username
     * @param string $password
     * @param string $name
     * @throws InitiateDatabaseException
     */
    public function initiateDatabase(string $host, int $port, string $username, string $password, string $name): void
    {
        $this->dbConnection = $this->getDatabaseConnection($host, $port, $username, $password, $name);

        $this->initiateSqlFiles();
        $this->executeMigrations();
    }

    /**
     * @throws InitiateDatabaseException
     */
    private function initiateSqlFiles(): void
    {
        $sqlFilePath = $this->getSetupDirectory();
        $this->executeSqlQueryFromFile(Path::join($sqlFilePath, 'Sql', "database_schema.sql"));
        $this->executeSqlQueryFromFile(Path::join($sqlFilePath, 'Sql', "initial_data.sql"));
    }

    /**
     * Method forms path to setup directory.
     *
     * @return string
     */
    public function getSetupDirectory(): string
    {
        return (getenv('SHOP_SETUP_PATH')) ?
            getenv('SHOP_SETUP_PATH') :
             Path::join($this->context->getCommunityEditionSourcePath(), 'Setup');
    }

    /**
     * @throws InitiateDatabaseException
     */
    private function executeMigrations(): void
    {
        try {
            $migrations = $this->createMigrations();
            $migrations->execute(Migrations::MIGRATE_COMMAND);
        } catch (Throwable $exception) {
            throw new InitiateDatabaseException(
                InitiateDatabaseException::EXECUTE_MIGRATIONS_PROBLEM,
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @return Migrations
     */
    private function createMigrations(): Migrations
    {
        $migrationsBuilder = new MigrationsBuilder();
        return $migrationsBuilder->build();
    }
    /**
     * @param string $sqlFilePath
     *
     * @throws InitiateDatabaseException
     */
    private function executeSqlQueryFromFile(string $sqlFilePath): void
    {
        $queries = file_get_contents($sqlFilePath);
        if (!$queries) {
            throw new InitiateDatabaseException(InitiateDatabaseException::READ_SQL_FILE_PROBLEM);
        }

        $this->dbConnection->exec($queries);
    }

    private function getDatabaseConnection(
        string $host,
        int $port,
        string $username,
        string $password,
        string $name
    ): PDO {
        $dbConnection = new PDO(
            sprintf('mysql:host=%s;port=%s;dbname=%s', $host, $port, $name),
            $username,
            $password,
            [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']
        );
        $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbConnection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $dbConnection;
    }
}
