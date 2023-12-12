<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Lock;

use RuntimeException;
use Stu\Module\Config\StuConfigInterface;

final class LockManager implements LockManagerInterface
{
    private StuConfigInterface $config;

    public function __construct(StuConfigInterface $config)
    {
        $this->config = $config;
    }

    public function setLock(int $batchGroupId, LockTypeEnum $type): void
    {
        $result = @touch($this->getLockPath($batchGroupId, $type));

        if ($result === false) {
            throw new RuntimeException(sprintf(
                'lock with batchGroupId "%d" of type "%d" could not be created',
                $type->value,
                $batchGroupId
            ));
        }
    }

    public function clearLock(int $batchGroupId, LockTypeEnum $type): void
    {
        $result = @unlink($this->getLockPath($batchGroupId, $type));

        if ($result === false) {
            throw new RuntimeException(sprintf(
                'lock with batchGroupId "%d" of type "%d" could not be deleted',
                $type->value,
                $batchGroupId
            ));
        }
    }

    public function isLocked(int $entityId, LockTypeEnum $type): bool
    {
        return @file_exists($this->getLockPath($entityId % $this->getGroupCount($type) + 1, $type));
    }

    private function getLockPath(int $batchGroupId, LockTypeEnum $type): string
    {
        return sprintf(
            '%s/%s_%d.lock',
            $this->config->getGameSettings()->getTempDir(),
            $type->getName(),
            $batchGroupId
        );
    }

    private function getGroupCount(LockTypeEnum $type): int
    {
        switch ($type) {
            case LockTypeEnum::COLONY_GROUP:
                return $this->config->getGameSettings()->getColonySettings()->getTickWorker();
            case LockTypeEnum::SHIP_GROUP:
                return 1;
        }
    }
}
