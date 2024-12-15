<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\BuildTorpedos;

use Override;
use request;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;

final class BuildTorpedos implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_BUILD_TORPEDOS';

    public function __construct(private ColonyLoaderInterface $colonyLoader, private TorpedoTypeRepositoryInterface $torpedoTypeRepository, private StorageManagerInterface $storageManager, private ColonyRepositoryInterface $colonyRepository) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::indInt('id'),
            $userId
        );

        $buildableTorpedoTypes = $this->torpedoTypeRepository->getForUser($userId);

        $torps = request::postArray('torp');
        $storage = $colony->getStorage();
        $msg = [];
        foreach ($torps as $torp_id => $count) {
            if (!array_key_exists($torp_id, $buildableTorpedoTypes)) {
                continue;
            }
            $count = (int)$count;
            $torp = $buildableTorpedoTypes[$torp_id];
            if ($torp->getEnergyCost() * $count > $colony->getEps()) {
                $count = floor($colony->getEps() / $torp->getEnergyCost());
            }
            if ($count <= 0) {
                continue;
            }
            foreach ($torp->getProductionCosts() as $cost) {
                if (!$storage->containsKey($cost->getCommodityId())) {
                    $count = 0;
                    break;
                }
                if ($count * $cost->getAmount() > $storage[$cost->getCommodityId()]->getAmount()) {
                    $count = floor($storage[$cost->getCommodityId()]->getAmount() / $cost->getAmount());
                }
            }
            if ($count == 0) {
                continue;
            }

            //count could be float here
            $count = (int)$count;

            foreach ($torp->getProductionCosts() as $cost) {
                $this->storageManager->lowerStorage($colony, $cost->getCommodity(), $cost->getAmount() * $count);
            }

            $this->storageManager->upperStorage($colony, $torp->getCommodity(), $count * $torp->getProductionAmount());

            $msg[] = sprintf(
                _('Es wurden %d Torpedos des Typs %s hergestellt'),
                $count * $torp->getProductionAmount(),
                $torp->getName()
            );
            $colony->lowerEps($count * $torp->getEnergyCost());
        }
        $this->colonyRepository->save($colony);

        if ($msg !== []) {
            $game->addInformationMerge($msg);
        } else {
            $game->addInformation(_('Es wurden keine Torpedos hergestellt'));
        }
        $game->setView(ShowColony::VIEW_IDENTIFIER);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
