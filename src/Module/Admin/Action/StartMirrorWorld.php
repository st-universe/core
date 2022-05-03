<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action;

use Stu\Module\Admin\View\Scripts\ShowScripts;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;

final class StartMirrorWorld implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_MIRROR_START';

    public const MIRROR_WORLD_DUMP_NAME = 'mirrorWorld.dump';

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowScripts::VIEW_IDENTIFIER);

        // only Admins can trigger ticks
        if (!$game->getUser()->isAdmin()) {
            $game->addInformation(_('[b][color=FF2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        $this->cleanup();
        $this->backupDatabase();
        $this->restoreDatabase();

        $game->addInformation('die Spiegelwelt wurde erstellt');
    }

    private function cleanup(): void
    {
        $backup_dir = $this->config->get('db.backup_dir');

        $dir = dir($backup_dir);
        while ($file = $dir->read()) {
            $filename = sprintf('%s/%s', $backup_dir, $file);

            if (is_file($filename) && $filename === self::MIRROR_WORLD_DUMP_NAME) {
                unlink($filename);
            }
        }
        $dir->close();
    }

    private function backupDatabase(): void
    {
        $cmd = sprintf(
            'PGPASSWORD="%s" pg_dump -Fc -U %s -h %s %s > %s/%s',
            $this->config->get('db.pass'),
            $this->config->get('db.user'),
            $this->config->get('db.host'),
            'mirrorFrom', //$this->config->get('db.database'), TODO reactivate after testing!
            $this->config->get('db.backup_dir'),
            self::MIRROR_WORLD_DUMP_NAME
        );

        system($cmd);
    }

    private function restoreDatabase(): void
    {
        $cmd = sprintf(
            //pg_restore -d stu -U postgres -C dd-MM-yyyy.dump
            'PGPASSWORD="%s" pg_restore -D %s -U %s -h %s -C %s/%s',
            $this->config->get('db.pass'),
            'mirrorTo',
            $this->config->get('db.user'),
            $this->config->get('db.host'),
            $this->config->get('db.backup_dir'),
            self::MIRROR_WORLD_DUMP_NAME
        );

        system($cmd);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
