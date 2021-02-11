<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\DeveloperTools\Framework\Database\Service;

use OxidEsales\DeveloperTools\Framework\Database\Exception\DatabaseConnectionException;
use OxidEsales\DeveloperTools\Framework\Database\Exception\DatabaseExistsException;
use OxidEsales\DeveloperTools\Framework\Database\Exception\DatabaseViewsGeneratorException;
use OxidEsales\DeveloperTools\Framework\Database\Exception\InitiateDatabaseException;
use PDOException;

/**
 * Class ResetDatabaseService
 *
 * @package OxidEsales\DeveloperTools\Framework\Database\Service
 */
class ResetDatabaseService implements ResetDatabaseServiceInterface
{
    /**
     * @var DatabaseCheckerInterface
     */
    private $databaseChecker;

    /**
     * @var DatabaseCreatorInterface
     */
    private $databaseCreator;

    /**
     * @var DatabaseInitiatorInterface
     */
    private $databaseInitiator;

    /**
     * @var DropDatabaseServiceInterface
     */
    private $dropDatabaseService;

    /**
     * @var DatabaseViewsGeneratorInterface
     */
    private $viewsGenerator;

    public function __construct(
        DatabaseCheckerInterface $databaseChecker,
        DatabaseCreatorInterface $databaseCreator,
        DatabaseInitiatorInterface $databaseInitiator,
        DropDatabaseServiceInterface $dropDatabaseService,
        DatabaseViewsGeneratorInterface $viewsGenerator
    ) {
        $this->databaseChecker = $databaseChecker;
        $this->databaseCreator = $databaseCreator;
        $this->databaseInitiator = $databaseInitiator;
        $this->dropDatabaseService = $dropDatabaseService;
        $this->viewsGenerator = $viewsGenerator;
    }

    /**
     * @param string $host
     * @param int $port
     * @param string $username
     * @param string $password
     * @param string $name
     *
     * @throws PDOException|InitiateDatabaseException|DatabaseConnectionException
     * @throws DatabaseViewsGeneratorException
     */
    public function resetDatabase(string $host, int $port, string $username, string $password, string $name): void
    {
        if ($this->databaseChecker->doesDatabaseExist($host, $port, $username, $password, $name)) {
            $this->dropDatabaseService->dropDatabase($host, $port, $username, $password, $name);
        }
        try {
            $this->databaseCreator->createDatabase($host, $port, $username, $password, $name);
        } catch (DatabaseExistsException $exception) {
        }

        $this->databaseInitiator->initiateDatabase($host, $port, $username, $password, $name);

        $this->viewsGenerator->generate();
    }
}
