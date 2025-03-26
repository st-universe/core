<?php

declare(strict_types=1);

namespace Stu\Module\NPC\View\ShowShipCreator;

use Override;
use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;
use Stu\Orm\Entity\SpacecraftBuildplanInterface;



final class ShowShipCreator implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SHIP_CREATOR';

    public function __construct(
        private SpacecraftBuildplanRepositoryInterface $spacecraftBuildplanRepository,
        private UserRepositoryInterface $userRepository,
        private LayerRepositoryInterface $layerRepository,
        private TorpedoTypeRepositoryInterface $torpedoTypeRepository,
        private SpacecraftRumpRepositoryInterface $spacecraftRumpRepository
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = request::getInt('userId');
        $buildplanId = request::getInt('buildplanId');

        $game->setTemplateFile('html/npc/shipCreator.twig');
        $game->appendNavigationPart('/npc/index.php?SHOW_SHIP_CREATOR=1', 'Schiff erstellen');
        $game->setPageTitle('Schiff erstellen');

        $npcList = iterator_to_array($this->userRepository->getNpcList());
        $nonNpcList = iterator_to_array($this->userRepository->getNonNpcList());
        $allUsers = array_merge($npcList, $nonNpcList);
        $game->setTemplateVar('ALL_USERS', $allUsers);

        if ($userId > 0) {
            $selectedUser = $this->userRepository->find($userId);
            $game->setTemplateVar('USER_ID', $userId);
            $game->setTemplateVar('SELECTED_USER', $selectedUser);

            if ($buildplanId > 0) {
                $buildplan = $this->spacecraftBuildplanRepository->find($buildplanId);
                if ($buildplan !== null) {

                    $rump = $buildplan->getRump();

                    $allRumps = iterator_to_array($this->spacecraftRumpRepository->getList());
                    $filteredRumps = array_filter($allRumps, fn($rump) => $rump->getNpcBuildable() === true);

                    $isRumpInFiltered = false;
                    foreach ($filteredRumps as $filteredRump) {
                        if ($filteredRump->getId() === $rump->getId()) {
                            $isRumpInFiltered = true;
                            break;
                        }
                    }

                    if (!$isRumpInFiltered) {
                        $game->addInformation('Dieser Rumpf darf nicht gebaut werden!');
                        return;
                    }


                    $possibleTorpedoTypes = $this->torpedoTypeRepository->getByLevel($buildplan->getRump()->getTorpedoLevel());
                    $game->setTemplateVar('TORPEDO_TYPES', $possibleTorpedoTypes);
                    $game->setTemplateVar('SELECTED_BUILDPLAN', $buildplan);
                    $game->setTemplateVar('LAYERS', $this->layerRepository->findAll());
                }
            } else {
                $allRumps = iterator_to_array($this->spacecraftRumpRepository->getList());
                $allBuildplans = $this->spacecraftBuildplanRepository->getByUser($userId);
                $filteredBuildplans = array_filter($allBuildplans, function ($buildplan) use ($allRumps) {
                    $rump = $buildplan->getRump();
                    foreach ($allRumps as $rumpItem) {
                        if ($rumpItem->getId() === $rump->getId()  && $rumpItem->getNpcBuildable() === true) {
                            return true;
                        }
                    }
                    return false;
                });

                $filteredBuildplans = array_filter($allBuildplans, function ($buildplan) use ($allRumps) {
                    $rump = $buildplan->getRump();
                    foreach ($allRumps as $rumpItem) {
                        if ($rumpItem->getId() === $rump->getId() && $rumpItem->getNpcBuildable() === true) {
                            return true;
                        }
                    }
                    return false;
                });

                $game->setTemplateVar('BUILDPLANS', $filteredBuildplans);
                $game->setTemplateVar('DELETABLE_BUILDPLANS', array_filter($filteredBuildplans, fn($buildplan) => $this->isDeletable($buildplan)));
            }
        }
    }

    private function isDeletable(SpacecraftBuildplanInterface $buildplan): bool
    {
        return $buildplan->getSpacecraftCount() === 0;
    }
}
