<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset\Storage;

interface StorageResetInterface
{
    public function deleteAllTradeOffers(): void;

    public function deleteAllTorpedoStorages(): void;

    public function deleteAllStorages(): void;
}
