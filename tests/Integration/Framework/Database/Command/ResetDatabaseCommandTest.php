<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\DeveloperTools\Tests\Integration\Framework\Database\Command;

use InvalidArgumentException;
use OxidEsales\DeveloperTools\Framework\Database\Command\ResetDatabaseCommand;
use OxidEsales\DeveloperTools\Framework\Database\Service\DatabaseCheckerInterface;
use OxidEsales\DeveloperTools\Framework\Database\Service\DatabaseCreatorInterface;
use OxidEsales\DeveloperTools\Framework\Database\Service\DatabaseInitiatorInterface;
use OxidEsales\DeveloperTools\Framework\Database\Service\DropDatabaseServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class ResetDatabaseCommandTest extends TestCase
{
    private const HOST = 'some-host';
    private const PORT = 123;
    private const DB = 'some-db';
    private const DB_USER = 'some-db-user';
    private const DB_PASS = 'some-db-pass';

    private $arguments = [
        '--db-host' => self::HOST,
        '--db-port' => self::PORT,
        '--db-name' => self::DB,
        '--db-user' => self::DB_USER,
        '--db-password' => self::DB_PASS
    ];

    public function testExecuteWithMissingArgs(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $databaseSetupCommand = new ResetDatabaseCommand(
            $this->getDatabaseCheckerMock(),
            $this->getDatabaseCreatorMock(),
            $this->getDatabaseInstallerMock(),
            $this->getDropDatabaseServiceMock()
        );
        $commandTester = new CommandTester($databaseSetupCommand);
        $commandTester->execute([]);
    }

    public function testExecuteWithExistingDatabaseAndWithForceParameter(): void
    {
        $databaseSetupCommand = new ResetDatabaseCommand(
            $this->getDatabaseCheckerWithExistingDbMock(),
            $this->getDatabaseCreatorMock(),
            $this->getDatabaseInstallerMock(),
            $this->getDropDatabaseServiceMock()
        );
        $commandTester = new CommandTester($databaseSetupCommand);

        $arguments = $this->arguments;
        $arguments['--force'] = true;
        $exitCode = $commandTester->execute($arguments);

        $this->assertSame(0, $exitCode);
        $this->assertNotFalse(strpos($commandTester->getDisplay(), 'Reset has been finished.'));
      //  $this->assertStringEndsWith('Reset has been finished.', $commandTester->getDisplay());
    }

    public function testExecuteOnEmptyDatabase(): void
    {
        $databaseSetupCommand = new ResetDatabaseCommand(
            $this->getDatabaseCheckerMock(),
            $this->getDatabaseCreatorMock(),
            $this->getDatabaseInstallerMock(),
            $this->getDropDatabaseServiceMock()
        );
        $commandTester = new CommandTester($databaseSetupCommand);

        $exitCode = $commandTester->execute($this->arguments);

        $this->assertSame(0, $exitCode);
        $this->assertNotFalse(strpos($commandTester->getDisplay(), 'Reset has been finished.'));
       // $this->assertStringEndsWith('Reset has been finished.', $commandTester->getDisplay());
    }

    public function testExecuteWithExistingDatabaseAndConfirmedAction(): void
    {
        $commandTester = new CommandTester($this->getCommandWithInteraction());
        $commandTester->setInputs(['yes']);

        $exitCode = $commandTester->execute($this->arguments);

        $this->assertSame(0, $exitCode);
        $this->assertNotFalse(strpos($commandTester->getDisplay(), 'Reset has been finished.'));
       // $this->assertStringEndsWith('Reset has been finished', $commandTester->getDisplay());
    }

    public function testExecuteWithExistingDatabaseAndRejectedAction(): void
    {
        $commandTester = new CommandTester($this->getCommandWithInteraction());
        $commandTester->setInputs(['no']);
        $exitCode = $commandTester->execute($this->arguments);

        $this->assertSame(0, $exitCode);
      //  $this->assertStringEndsWith('Reset has been canceled.', $commandTester->getDisplay());
        $this->assertNotFalse(strpos($commandTester->getDisplay(), 'Reset has been canceled.'));
    }

    private function getCommandWithInteraction(): Command
    {
        $databaseSetupCommand = new ResetDatabaseCommand(
            $this->getDatabaseCheckerWithExistingDbMock(),
            $this->getDatabaseCreatorMock(),
            $this->getDatabaseInstallerMock(),
            $this->getDropDatabaseServiceMock()
        );
        $databaseSetupCommand->setName('oe:database:reset');

        $application = new Application();
        $application->add($databaseSetupCommand);
        return $application->find('oe:database:reset');
    }

    private function getDatabaseCheckerWithExistingDbMock(): DatabaseCheckerInterface
    {
        $databaseChecker = $this->prophesize(DatabaseCheckerInterface::class);
        $databaseChecker->doesDatabaseExist(
            self::HOST,
            self::PORT,
            self::DB_USER,
            self::DB_PASS,
            self::DB
        )
            ->willReturn(true);
        return $databaseChecker->reveal();
    }

    private function getDatabaseCheckerMock(): DatabaseCheckerInterface
    {
        $databaseChecker = $this->prophesize(DatabaseCheckerInterface::class);
        $databaseChecker->doesDatabaseExist(
            self::HOST,
            self::PORT,
            self::DB_USER,
            self::DB_PASS,
            self::DB
        )
            ->willReturn(false);
        return $databaseChecker->reveal();
    }

    private function getDatabaseCreatorMock(): DatabaseCreatorInterface
    {
        return $this->prophesize(DatabaseCreatorInterface::class)->reveal();
    }

    private function getDatabaseInstallerMock(): DatabaseInitiatorInterface
    {
        return $this->prophesize(DatabaseInitiatorInterface::class)->reveal();
    }

    private function getDropDatabaseServiceMock(): DropDatabaseServiceInterface
    {
        return $this->prophesize(DropDatabaseServiceInterface::class)->reveal();
    }
}
