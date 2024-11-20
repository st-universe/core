<?php

namespace Stu\Module\Config\Model;

interface GameSettingsInterface
{
    /** @return array<int> */
    public function getAdminIds(): array;

    public function getTempDir(): string;

    public function useSemaphores(): bool;

    public function getVersion(): string|int;

    public function getWebroot(): string;

    public function getAdminSettings(): AdminSettingsInterface;

    public function getColonySettings(): ColonySettingsInterface;

    public function getEmailSettings(): EmailSettingsInterface;

    public function getMapSettings(): MapSettingsInterface;

    public function getPirateLogfilePath(): string;
}
