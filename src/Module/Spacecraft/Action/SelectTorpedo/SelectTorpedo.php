<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\SelectTorpedo;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Repository\TorpedoStorageRepositoryInterface;

final class SelectTorpedo implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SELECT_TORPEDO';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private TorpedoStorageRepositoryInterface $torpedoStorageRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        if (!request::has('torpedo_type')) {
            return;
        }

        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );
        $spacecraft = $wrapper->get();
        $torpedoTypeId = request::getIntFatal('torpedo_type');

        $selectedStorage = null;
        foreach ($spacecraft->getFireableTorpedoStorages() as $torpedoStorage) {
            if ($torpedoStorage->getTorpedo()->getId() === $torpedoTypeId) {
                $selectedStorage = $torpedoStorage;
                break;
            }
        }

        if ($selectedStorage === null) {
            $game->getInfo()->addInformation('Der ausgewählte Torpedotyp ist nicht schussbereit geladen');
            return;
        }

        foreach ($spacecraft->getTorpedoStorages() as $torpedoStorage) {
            $torpedoStorage->setActive($torpedoStorage === $selectedStorage);
            $this->torpedoStorageRepository->save($torpedoStorage);
        }

        $game->getInfo()->addInformationf('%s ist nun als schussbereiter Torpedotyp ausgewählt', $selectedStorage->getTorpedo()->getName());
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
