<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\BuildTorpedos;

use request;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use TorpedoType;

final class BuildTorpedos implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_BUILD_TORPEDOS';

    private $colonyLoader;

    public function __construct(
        ColonyLoaderInterface $colonyLoader
    ) {
        $this->colonyLoader = $colonyLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $userId
        );

        $torps = request::postArray('torps');
        $torpedo_types = TorpedoType::getBuildableTorpedoTypesByUser($userId);
        $storage = $colony->getStorage();
        $msg = array();
        foreach ($torps as $torp_id => $count) {
            if (!array_key_exists($torp_id, $torpedo_types)) {
                continue;
            }
            $count = intval($count);
            $torp = $torpedo_types[$torp_id];
            if ($torp->getECost() * $count > $colony->getEps()) {
                $count = floor($colony->getEps() / $torp->getEcost());
            }
            if ($count <= 0) {
                continue;
            }
            foreach ($torp->getCosts() as $id => $cost) {
                if (!$storage[$cost->getGoodId()]) {
                    $count = 0;
                    break;
                }
                if ($count * $cost->getAmount() > $storage[$cost->getGoodId()]->getAmount()) {
                    $count = floor($storage[$cost->getGoodId()]->getAmount() / $cost->getAmount());
                }
            }
            if ($count == 0) {
                continue;
            }
            foreach ($torp->getCosts() as $id => $cost) {
                $colony->lowerStorage($cost->getGoodId(), $cost->getAmount() * $count);
            }
            $colony->upperStorage($torp->getGoodId(), $count * $torp->getAmount());
            $msg[] = sprintf(
                _('Es wurden %d Torpedos des Typs %s hergestellt'),
                $count * $torp->getAmount(),
                $torp->getName()
            );
            $colony->lowerEps($count * $torp->getECost());
        }
        $colony->save();
        if (count($msg) > 0) {
            $game->addInformationMerge($msg);
        } else {
            $game->addInformation(_('Es wurden keine Torpedos hergestellt'));
        }
        $game->setView(ShowColony::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
