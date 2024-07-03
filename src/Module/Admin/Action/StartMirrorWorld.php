<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action;

use Noodlehaus\ConfigInterface;
use Override;
use Stu\Module\Admin\View\Scripts\ShowScripts;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;

final class StartMirrorWorld implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_MIRROR_START';

    public const string MIRROR_FROM_DB_NAME = 'stu';
    public const string MIRROR_TO_DB_NAME = 'mirrorto';
    public const string MIRROR_WORLD_DUMP_NAME = 'mirrorWorld.dump';

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        private ConfigInterface $config,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowScripts::VIEW_IDENTIFIER);

        // only Admins can trigger ticks
        if (!$game->isAdmin()) {
            $game->addInformation(_('[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        $this->loggerUtil->init('mirror', LoggerEnum::LEVEL_ERROR);

        $this->cleanup();

        if (!$this->backupDatabase($game)) {
            return;
        }
        if (!$this->dropDatabse($game)) {
            return;
        }
        if (!$this->createDatabse($game)) {
            return;
        }
        if (!$this->restoreDatabase($game)) {
            return;
        }

        $game->addInformation('die Spiegelwelt wurde erstellt');
    }

    private function cleanup(): void
    {
        $backup_dir = $this->config->get('db.backup_dir');

        $dir = dir($backup_dir);
        while ($file = $dir->read()) {
            $filename = sprintf('%s/%s', $backup_dir, $file);

            if (is_file($filename) && $filename === self::MIRROR_WORLD_DUMP_NAME) {
                $this->loggerUtil->log('mirrorWorld dump deleted');
                unlink($filename);
            }
        }
        $dir->close();
    }

    private function dropDatabse(GameControllerInterface $game): bool
    {
        $cmd = sprintf(
            'PGPASSWORD="%s" dropdb -U %s -h %s --if-exists %s',
            $this->config->get('db.pass'),
            $this->config->get('db.user'),
            $this->config->get('db.host'),
            self::MIRROR_TO_DB_NAME,
        );

        $this->loggerUtil->log(sprintf('dropDatabase: %s', $cmd));

        if (!system($cmd)) {
            $game->addInformation('drop database failed');
            //return false;
        }

        return true;
    }

    private function createDatabse(GameControllerInterface $game): bool
    {
        $cmd = sprintf(
            'PGPASSWORD="%s" createdb -U %s -h %s -T template0 %s',
            $this->config->get('db.pass'),
            $this->config->get('db.user'),
            $this->config->get('db.host'),
            self::MIRROR_TO_DB_NAME,
        );

        $this->loggerUtil->log(sprintf('createDatabase: %s', $cmd));

        if (!system($cmd)) {
            $game->addInformation('create database failed');
            //return false;
        }

        return true;
    }

    private function backupDatabase(GameControllerInterface $game): bool
    {
        $cmd = sprintf(
            'PGPASSWORD="%s" pg_dump -Fc -U %s -h %s %s > %s/%s',
            $this->config->get('db.pass'),
            $this->config->get('db.user'),
            $this->config->get('db.host'),
            self::MIRROR_FROM_DB_NAME, //$this->config->get('db.database'), TODO reactivate after testing!
            $this->config->get('db.backup_dir'),
            self::MIRROR_WORLD_DUMP_NAME
        );

        $this->loggerUtil->log(sprintf('backupDatabase: %s', $cmd));

        if (!system($cmd)) {
            $game->addInformation('backup failed');
            //return false;
        }

        return true;
    }

    private function restoreDatabase(GameControllerInterface $game): bool
    {
        $cmd = sprintf(
            //pg_restore -d stu -U postgres -C dd-MM-yyyy.dump
            'PGPASSWORD="%s" pg_restore -d %s -U %s -h %s -C %s/%s',
            $this->config->get('db.pass'),
            self::MIRROR_TO_DB_NAME,
            $this->config->get('db.user'),
            $this->config->get('db.host'),
            $this->config->get('db.backup_dir'),
            self::MIRROR_WORLD_DUMP_NAME
        );

        $this->loggerUtil->log(sprintf('restoreDatabase: %s', $cmd));

        if (!system($cmd)) {
            $game->addInformation('restore failed');
            //return false;
        }

        return true;
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
