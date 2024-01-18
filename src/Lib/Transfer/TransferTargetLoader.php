<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer;

use RuntimeException;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;

class TransferTargetLoader implements TransferTargetLoaderInterface
{
    private ColonyLoaderInterface $colonyLoader;

    private ShipLoaderInterface $shipLoader;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShipLoaderInterface $shipLoader
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->shipLoader = $shipLoader;
    }
    public function loadTarget(int $targetId, bool $isColonyTarget, bool $checkForEntityLock = true): ShipInterface|ColonyInterface
    {
        if ($isColonyTarget) {
            $target =  $this->colonyLoader->load($targetId, $checkForEntityLock);
        } else {
            $target =  $this->shipLoader->find($targetId, $checkForEntityLock);
        }

        if ($target === null) {
            throw new RuntimeException(sprintf(
                'target %s with id %d does not exist',
                $isColonyTarget ? 'colony' : 'ship',
                $targetId
            ));
        }

        return $target instanceof ColonyInterface
            ? $target
            : $target->get();
    }
}
