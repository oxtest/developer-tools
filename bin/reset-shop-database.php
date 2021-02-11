<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

use OxidEsales\DeveloperTools\Framework\Database\Service\ResetDatabaseServiceInterface;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\Facts\Facts;
use Psr\Container\ContainerInterface;

$autoloadFiles = [
    __DIR__ . '/vendor/autoload.php',
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../vendor/autoload.php',
    __DIR__ . '/../../../vendor/autoload.php',
    __DIR__ . '/../../../../vendor/autoload.php',
];

$autoloadFileExist = false;
foreach ($autoloadFiles as $autoloadFile) {
    if (file_exists($autoloadFile)) {
        require_once $autoloadFile;
        $autoloadFileExist = true;
        break;
    }
}
if (!$autoloadFileExist) {
    exit("Autoload file was not found!");
}

setupShopDatabase();

function setupShopDatabase()
{
    $facts = new Facts();

    $resetDatabaseService = getContainer()->get(ResetDatabaseServiceInterface::class);
    $resetDatabaseService->resetDatabase(
        $facts->getDatabaseHost(),
        $facts->getDatabasePort(),
        $facts->getDatabaseUserName(),
        $facts->getDatabasePassword(),
        $facts->getDatabaseName()
    );
}

function getContainer(): ContainerInterface
{
    return ContainerFactory::getInstance()->getContainer();
}