<?php

declare(strict_types=1);

namespace Stu\Module\Config\Model;

interface GameSettingsInterface
{
    /** @return array<int> */
    public function getAdminIds(): array;

    public function getHashMethod(): string;

    public function getTempDir(): string;

    public function getVersion(): string|int;

    public function getWebroot(): string;

    public function getAdminSettings(): AdminSettingsInterface;

    public function getColonySettings(): ColonySettingsInterface;

    public function getEmailSettings(): EmailSettingsInterface;

    /**
     * @return array<array{feature: string, userIds: array<int>}>
     */
    public function getGrantedFeatures(): array;

    public function getMapSettings(): MapSettingsInterface;

    public function getPirateSettings(): PirateSettingsInterface;
}
